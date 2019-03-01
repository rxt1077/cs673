#!/bin/bash

# Deploys website on AFS

rm -r ~/public_html/takestock
cp -r takestock ~/public_html/takestock

sed -i s/127.0.0.1/sql.njit.edu/ ~/public_html/include/db.php
