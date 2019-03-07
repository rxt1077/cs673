#!/bin/bash

# Deploys website on AFS

rm -r ~/public_html/takestock
cp -r takestock ~/public_html/takestock

sed "s/.*\$servername.*=.*/\$servername = 'sql.njit.edu';/" takestock/include/db.php > ~/public_html/takestock/include/db.php
sed "s/.*\$basedir.*=.*/    \$basdir = '\/~rxt1077\/takestock';/" takestock/config.php > ~/public_html/takestock/config.php
