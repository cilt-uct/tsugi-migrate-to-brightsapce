# Migrate to Brightspace [LTI] (tsugi-migrate-to-brightspace)

Used on a Sakai Site to invoke the migration process (https://github.com/cilt-uct/Brightspace-migrate-from-sakai), and report back to user when the archive has been imported into Brightspace.

## Installation
Install through the "Tsugi - Admin" interface and run "Update Database" to create the appropriate database tables.

## Process
The UI just updates the database entry for the site in `migration` and `migration_site` and shows the result of the process as the entry transitions through the various states (`init,starting,exporting,running,queued,uploading,importing,updating,completed,error`).

## Custom LTI Parameters
The UI and functionality can change custom parameter that can be set for the tool:
 (ordered by importance).
 
 1. `superadmin=true` : View changes to show ALL migration sites in this Tsugi installation.
 2. `admin=true` : Allows the batch migrations of sites.
 3. `dev=true` : Sets the tool into development mode, which shows the 'coming soon' page except if the site id is configured in the configuration file.

## Configuration
Create a local configuration file and then update it to your settings:
```
cp tool-config_dist.php tool-config.php
```

## SOAP
For SoapClient to work make sure it is enabled and installed in PHP:
```
sudo apt install php8.0-soap
```