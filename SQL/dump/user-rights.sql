GRANT SELECT ON all tables in schema "public" TO routes;
GRANT EXECUTE ON function lo_import(text) to routes;
GRANT INSERT, DELETE ON temp_bsd_site_id,  temp_floyd_warshall,
temp_geojson_string, temp_geojson_strings, temp_json_object, temp_selected_bsd_site_ids,
temp_visited_points, temp_fairify_selection, routes_for_app_display, routes, temp_organism_name,
temp_bytea, notes to routes;
GRANT INSERT ON lookup_routes to routes;
GRANT SELECT, UPDATE, USAGE ON SEQUENCE lookup_routes_id_seq TO routes;
GRANT SELECT, INSERT On lookup_users, exif, exif_error, bsd_site, bsd_site_deleteme  to routes;
GRANT UPDATE ON exif, notes TO routes;
GRANT SELECT, UPDATE, USAGE ON SEQUENCE exif_id_seq, notes_id_seq TO routes;