#!/bin/bash

ddev drush sql:sanitize
ddev export-db --file=./assets/database.sql.gz
