# Install
To install, a priviledge file has to be created: `/usr/share/iserv/priv/` containing the following:
```
mod_nachschreibarbeiten_access:
module          Nachschreibarbeiten
description     Access the Nachschreibarbeiten module
assign          admins

mod_nachschreibarbeiten_admin:
module          Nachschreibarbeiten
description     Administer the Nachschreibarbeiten module
assign          admins
```

Also, this has to be added to the old version's `/usr/share/iserv/db/mod_iserv-nachschreibarbeiten.sql`:
```
-- Iserv 3
GRANT ALL ON mod_nachschreibarbeiten_dates, mod_nachschreibarbeiten_entries TO symfony;
GRANT USAGE, SELECT ON SEQUENCE mod_nachschreibarbeiten_dates_id_seq, mod_nachschreibarbeiten_entries_id_seq TO symfony;
```

Additionally, another file has to be created `/usr/share/iserv/iservcfg/config/30mod_iserv-nachschreibarbeiten`:
```
NachschreibarbeitenInfotext:
name        Infotext im Nachschreibarbeitenmodul
description Im Nachschreibarbeitenmodul kann ein Infotext angezeigt werden, welcher hier eingestellt werden kann.
group       Module: Nachschreibarbeiten
type        string
default         "Die Aufgaben für die Nachschreiber_innen werden im Fach „Nachschreibarbeiten“, das sich unter den Lehrer_innenpostfächern im Heidberg befindet, gesammelt. Zusätzliche Materialien (z.B. Duden, Atlas, Taschenrechner etc.) müssen von den betroffenen Fachlehrkräften organisiert und im entsprechenden Raum (i.d.R. Raum 151, Ausnahmen s. Liste unten) bereitgestellt werden. Einträge in die Liste der Nachschreiber_innen erfolgen bitte bis spätestens zum Vortag (16:00 Uhr) des jeweiligen Nachschreibtermins."
```
