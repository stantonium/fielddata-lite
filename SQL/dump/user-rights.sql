GRANT SELECT ON all tables in schema "public" TO routes;
GRANT EXECUTE ON function lo_import(text) to routes;
GRANT INSERT, DELETE ON temp_bsd_site_id,  temp_floyd_warshall, temp_selected_bsd_site_ids,
temp_visited_points, temp_fairify_selection, routes_for_app_display, routes, notes to routes;
GRANT INSERT ON lookup_routes to routes;
GRANT UPDATE ON notes TO routes;
GRANT SELECT, UPDATE, USAGE ON SEQUENCE lookup_routes_id_seq, notes_id_seq TO routes;