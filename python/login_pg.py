# Programmer: Dan Hopp
# Date: 30-SEP-2022
# Description: Log into postgresql for various users. Return connection.
#
# Database connection uses psycopg2:
#   pip install psycopg2
#   May need to first install pg_config:
#       sudo apt install libpq-dev
#   For testing and development a stand-alone package can work (Not recommended for production. Source recommends to use its distribution.)
#       pip install psycopg2-binary
#
# If SSH tunnel is required:
#   pip install sshtunnel

import psycopg2
# from sshtunnel import SSHTunnelForwarder
import numpy as np

# To help change pandas NaN to null for SQL
def nan_to_null(f,
        _NULL=psycopg2.extensions.AsIs('NULL'),
        _Float=psycopg2.extensions.Float):
    if not np.isnan(f):
        return _Float(f)
    return _NULL

# To allow for numpy's NaNs-to-null convesion for DB table insertion
psycopg2.extensions.register_adapter(float, nan_to_null)

# def login_pg_fielddata_ssh():

#     ssh_tunnel = SSHTunnelForwarder(
#         ('equipment.ornl.gov', 22),
#         ssh_username = '',
#         # ssh_password='',
#         ssh_private_key = 'C:\\Users\\o9h\\SSH\\bsd-cpb-putty-to-openSSH',
#         # ssh_private_key = '/home/o9h/Image-ExifTool-12.45/ssh/bsd-cpb-putty-to-openSSH',
#         remote_bind_address=('localhost', 5432)
#         # remote_bind_address = ('equipment.ornl.gov', 5432) ,
#         # local_bind_address=('localhost', 8080)
#         )
#     ssh_tunnel.start()

#     # SSHTunnelForwarder.check_tunnels()
#     # print(SSHTunnelForwarder.tunnel_is_up)

#     conn = psycopg2.connect(
#         database = 'fielddata_lite', 
#         user = 'routes',
#         password = 'chikin',
#         host = 'add password',
#         port = 5432)
#     return conn, ssh_tunnel

def login_pg_fielddata():

    conn = psycopg2.connect( # port forwarding to olcf connects fine
        database = 'fielddata_lite', 
        user = 'routes',
        password = 'add password',
        host = 'localhost',
        port = 5432)
    return conn

def close_connections(conn):
    conn.close()

def close_connections_ssh(conn, ssh_tunnel):
    conn.close()
    ssh_tunnel.stop()

def check_connection(conn):
    #Creating a cursor object using the cursor() method
    cursor = conn.cursor()

    #Executing a function using the execute() method
    cursor.execute("select version()")

    # Fetch a single row using fetchone() method.
    data = cursor.fetchone()
    print("Connection established to: ",data)

# # conn, ssh_tunnel = login_pg_fielddata_ssh()
# conn = login_pg_fielddata()

# # close_connections_ssh(conn, ssh_tunnel)
# close_connections(conn)