# Blabla (kannst du gern machen @baltpeter)

# Install
To install, a priviledge file has to be created: `/usr/share/iserv/priv/` containing the following:
```
nachschreibarbeiten_access:
module          Nachschreibarbeiten
description     Access the Nachschreibarbeiten module
assign          admins

nachschreibarbeiten_admin:
module          Nachschreibarbeiten
description     Administer the Nachschreibarbeiten module
assign          admins
```

Also, this has to be added to the old version's `usr/share/iserv/db/mod_iserv-nachschreibarbeiten`:
```
-- Iserv 3
GRANT ALL ON mod_nachschreibarbeiten_dates, mod_nachschreibarbeiten_entries TO symfony;
GRANT USAGE, SELECT ON SEQUENCE mod_nachschreibarbeiten_dates_id_seq, mod_nachschreibarbeiten_entries_id_seq TO symfony;
```