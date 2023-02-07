# get_exif_data function Authored by Ambarish Nag ambarish.nag@nrel.gov. Additional script functionality and fixes by Daniel Hopp 
#   danhopp42@gmail.com.
# Reverse-engineering Notes: Script is non-windows friendly. Final dataframe is filtered to a specific GPS Horizontal Positioning
#   Error threshold. Only .jpg files are read.
# Notes on exiftool's flags:
#   Add '-ee3' and '-api RequestAll=3' to the command to extract absolutely everything available.
#   Use the '-s' command-line option to see the actual tag names instead of the descriptions shown when extracting information.
#   -U Extract unknown binary tags too.
#   -ee[NUM] (-extractEmbedded)
#       Extract information from embedded documents in EPS files, embedded EPS information and JPEG and Jpeg2000 images in PDF 
#       files, embedded MPF images in JPEG and MPO files, streaming metadata in AVCHD videos, and the resource fork of Mac OS 
#       files. Implies the -a option. Use -g3 or -G3 to identify the originating document for extracted information. Embedded 
#       documents containing sub-documents are indicated with dashes in the family 3 group name. (eg. Doc2-3 is the 3rd 
#       sub-document of the 2nd embedded document.) Note that this option may increase processing time substantially, especially 
#       for PDF files with many embedded images or videos with streaming metadata.
#
# Database connection uses psycopg2:
#   pip install psycopg2
#   May need to first install pg_config:
#       sudo apt install libpq-dev
#   A stand-alone package can also work (Not recommended for production. Source recommends to use its distribution.)
#       pip install psycopg2-binary
# 
# Paths use a YAML config file:
#   pip install PyYAML
#
# Install ExifTool: https://exiftool.org/

# Changelog:
#   27-SEP-2022 Added functionality for files names with spaces. Added file count. - Dan Hopp
#   29-SEP-2022 Changed Lat and Long dataframe column names to lowercase. - Dan Hopp
#   03-OCT-2022 Run through subfolders. Include full path with filename. - Dan Hopp
#   04-OCT-2022 Change time_stamp date format to pg's format. Add geo_point column in dataframe. Fix issue where pos_error would
#       duplicate the previous pos_error if the image file was from a source that did not have horizontal accuracy (eg an older 
#       iPhone). - Dan Hopp
#   05-OCT-2022 Change time_stamp column name to img_timestamp. - Dan Hopp
#   07-OCT-2022 Add a logging into the database. Insert data into a temp table using execute_values. When script complete, call 
#       a DB funtion (query_exif_process_data()) to filter the tempdata table into its appropriate destination tables.
#   11-OCT-2022 Add passible file path to script call. Add try, except, rollback, and commit DB actions to other fuctions. - Dan Hopp
#   31-OCT-2022 Add YAML configuration file for file paths. Base path for folder crawl is still passed by cmd line. Also added an 
#       option to save EXIF data as a .csv instead of writing it to the database. - Dan Hopp
#   
#   


import os
from signal import SIG_DFL
import sys
import pandas as pd # Install if needed
import yaml
import glob # The glob module finds all the pathnames matching a specified pattern according to the rules used by the Unix shell,
            # although results are returned in arbitrary order.
import login_pg as login
import psycopg2
import psycopg2.extras as extras

# Get config file
def read_yaml():
    with open("config.yaml", "r") as f:
        return yaml.safe_load(f)

#### Define variables
config = read_yaml()
exifToolPath = config["EXIF_EXTRACT"]["exifToolPath"]
tempFilePath = config["EXIF_EXTRACT"]["tempFilePath"]
posErrThreshold = config["EXIF_EXTRACT"]["posErrThreshold"]
_pos_error_limit = config["EXIF_EXTRACT"]["_pos_error_limit"]
_point_within = config["EXIF_EXTRACT"]["_point_within"]
csvSavePath = config["EXIF_EXTRACT"]["csvSavePath"]

# For export to .csv option
exportToCSV = 'N'
insertDataCount = 0 

# Does the base path have no subdirectories?
hasNoSubdir = False

#### Define EXIF and Database functions
# Check if a path has no subdirectories
def check_if_an_only_path(imgPath):
    for root, subdirectories, files in os.walk(imgPath):
        if (subdirectories):
            return
        else:
            global hasNoSubdir
            hasNoSubdir = True
            return

# for each subfolder: run exif function
def cycle_through_folders(imgPath, conn):
    
    # Does the base path have no subdirectories?
    check_if_an_only_path(imgPath)

    global hasNoSubdir

    if (hasNoSubdir):
        get_exif_data(imgPath, conn)
    else:
        for root, subdirectories, files in os.walk(imgPath):
            for subdirectory in subdirectories:
                get_exif_data(os.path.join(root, subdirectory), conn)
            

# Call exiftool for every image in the path, extract data to txt file, parse txt data into dataframe, export dataframe
def get_exif_data(imgPath, conn):

    imagefilenames = glob.glob(os.path.join(imgPath, '*.jpg'))

    numberOfImages = len(imagefilenames)
    # skip folders with no pics
    if numberOfImages > 0:
        for i in range(0, numberOfImages):
            imagefile = imagefilenames[i]
            tmpfile = tempFilePath + 'temp_' + str(i) + '.txt'
            print(imagefile)
            ''' use Exif tool to write the metadata in a temp file'''
            cmd_exiftool = (exifToolPath + ' -ee3 -U -G3:1 -api requestall=3 -api largefilesupport "' +
                             imagefile + '" > ' + tmpfile)
            os.system(cmd_exiftool)
            ''' get the tags in dict '''
            with open(tmpfile, encoding='utf8', errors='ignore') as f:
                lines = f.readlines()

            infoDict = {} #Creating the dict to get the metadata tags and to avoid duplicate pos_err values.

            for line in lines:
                line = line.strip().split(': ')
                infoDict[" ".join(line[0].strip().split())] = line[-1].strip()
            df = pd.DataFrame.from_records([infoDict])
            parts_lat = df['[Composite] GPS Latitude'].str.extract('(\d+)(\s)deg(\s)(\d+)\'\s([^"]+)"(\s)([N|S])', expand=True)
            parts_lon = df['[Composite] GPS Longitude'].str.extract('(\d+)(\s)deg(\s)(\d+)\'\s([^"]+)"(\s)([E|W])', expand=True)
            df['latitude'] = (parts_lat[0].astype(float) + parts_lat[3].astype(float) / 60 + parts_lat[4].astype(float) / 3600) * parts_lat[6].map({'N':1, 'S':-1})
            df['longitude'] = (parts_lon[0].astype(float) + parts_lon[3].astype(float) / 60 + parts_lon[4].astype(float) / 3600) * parts_lon[6].map({'E': 1, 'W':-1})
            cols = ['latitude', 'longitude']
            df[cols] = df[cols].round(6)

            # Translate [Composite] Date/Time Original to pg's date format
            time_stamp = df.iloc[0]['[Composite] Date/Time Original']
            time_stamp = time_stamp[:10].replace(':', '-') + time_stamp[10:]
            df['img_timestamp'] = time_stamp

            # Add a geo_point column. Format is "POINT (Longitude Latitude)"
            df['geo_point'] = 'POINT (' + str(df.iloc[0]['longitude']) + ' ' + str(df.iloc[0]['latitude']) + ')'

            # Rename column headers
            df.rename({'[System] File Name': 'file_name', '[GPS] GPS Horizontal Positioning Error':'pos_err'}, axis=1, inplace=True)

            # get full path
            df['file_name'] = imagefile

            # some pics do not have [GPS] GPS Horizontal Positioning Error.
            if 'pos_err' in df:
                df['pos_err'] = df['pos_err'].str[:-2].astype(float)
            else:
                df['pos_err'] = None

            # record the database thresholds to be used
            global _pos_error_limit
            global _point_within 
            df['pos_err_limit'] = _pos_error_limit
            df['proximity_limit'] = _point_within

            # Get selected columns    
            df_sel = df[['img_timestamp','file_name','latitude','longitude','pos_err','geo_point', 'pos_err_limit', 'proximity_limit']]
            if (i == 0):
                df_comb = df_sel
            else:
                df_comb = pd.concat([df_comb, df_sel], axis=0)
                df_comb.reset_index(drop=True, inplace=True)
            
            cmd_rm = 'rm ' + tmpfile
            os.system(cmd_rm)
    
        # create a new dataframe filtered to a specific GPS Horizontal Positioning Error threshold
        global posErrThreshold
        df_export = df_comb
        dfNoPosErr = df_comb
        df_export = df_export.loc[df_export['pos_err'] < posErrThreshold]

        # Get non-HPE pics
        dfNoPosErr = df_comb[df_comb['pos_err'].isna()]
        df_export = pd.concat([df_export, dfNoPosErr], axis=0)
        df_export.reset_index(drop=True, inplace=True)

        # Save data as .csv(s)
        if (conn == ''):
            global insertDataCount
            df_export.to_csv(csvSavePath + 'img_exif_extract_' + str(insertDataCount) + '.csv', index = False)
            insertDataCount += 1
        # Upload to DB
        else:
            execute_values(conn, df_export, 'temp_exif_import') 

        print(str(numberOfImages) + ' jpg files read.')
        print(str(len(df_export)) + ' records with pos_err < ' + str(posErrThreshold) + 
            ' and pos_err = null inserted into the temp table.')

# Insert dataframe into temp table
# from https://naysan.ca/2020/05/09/pandas-to-postgresql-using-psycopg2-bulk-insert-performance-benchmark/
def execute_values(conn, df, table):
    """
    Using psycopg2.extras.execute_values() to insert the dataframe
    """
    # Create a list of tupples from the dataframe values
    tuples = [tuple(x) for x in df.to_numpy()]
    # Comma-separated dataframe columns
    cols = ','.join(list(df.columns))
    # SQL quert to execute
    query  = "INSERT INTO %s(%s) VALUES %%s" % (table, cols)
    cursor = conn.cursor()
    try:
        extras.execute_values(cursor, query, tuples)
        conn.commit()
    except (Exception, psycopg2.DatabaseError) as error:
        print("Error: %s" % error)
        conn.rollback()
        cursor.close()
        return 1
    cursor.close()

def clear_data_from_temp_table(conn):
    cursor = conn.cursor()
    try:
        cursor.execute("DELETE FROM public.temp_exif_import")
        conn.commit()
    except (Exception, psycopg2.DatabaseError) as error:
        print("Error: %s" % error)
        conn.rollback()
        cursor.close()
        return 1
    print('Data in the temp table has been deleted.')
    cursor.close()

# Call when cycle_through_folders() is complete:
def process_table_data(conn):
    # Insert the df data into the DB's temp table
    cursor = conn.cursor()
    try:
        # query_exif_process_data(_point_within NUMERIC, _pos_error_limit NUMERIC). _point_within is meters
        cursor.execute("CALL query_exif_process_data(%s, %s)" % (_point_within, _pos_error_limit))
        conn.commit()
    except (Exception, psycopg2.DatabaseError) as error:
        print("Error: %s" % error)
        conn.rollback()
        cursor.close()
        return 1
    print('Exif data has been processed.')
    cursor.close()


##############################
# -------BEGIN SCRIPT------- #
##############################

# script call is: [call to python] [path of script] [root path of images] [save as CSV instead? Y/N (Optional. Default is N)]

# Get root file path
imgPath = sys.argv[1]

# Save as .csv option
if (len(sys.argv) == 3):
    exportToCSV = sys.argv[2].upper()

# Save data as .csv(s)?
if (exportToCSV == 'Y'):
    cycle_through_folders(imgPath, '')
# Insert data into database?
else:
    # Login to DB
    conn = login.login_pg_fielddata()
    login.check_connection(conn)
    # Process exif data
    clear_data_from_temp_table(conn)
    cycle_through_folders(imgPath, conn)
    process_table_data(conn)
    login.close_connections(conn)
