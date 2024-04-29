GRANT usage ON SCHEMA fern TO routes;
GRANT usage ON SCHEMA public TO routes;
GRANT SELECT ON all tables in schema "public" TO routes;
GRANT SELECT ON all tables in schema fern TO routes;
GRANT EXECUTE ON function lo_import(text) to routes;
GRANT INSERT, DELETE, truncate ON temp_bsd_site_id,  temp_floyd_warshall,
temp_geojson_string, temp_geojson_strings, temp_json_object, temp_selected_bsd_site_ids,
temp_visited_points, temp_fairify_selection, temp_organism_name,
temp_bytea, fern.temp_trip to routes;
grant delete on routes_for_app_display, routes, notes to routes;
GRANT INSERT ON lookup_routes, fern.trip, fern.lookup_trip, fern.temp_trip to routes;
GRANT SELECT, UPDATE, USAGE ON SEQUENCE lookup_routes_id_seq TO routes;
GRANT INSERT On "user", exif, exif_error, bsd_site to routes;
GRANT INSERT, DELETE ON public.temp_exif_import TO routes;
GRANT UPDATE ON public.exif, notes TO routes;
GRANT SELECT, UPDATE, USAGE ON SEQUENCE public.exif_id_seq, notes_id_seq, lookup_routes_id_seq TO routes;