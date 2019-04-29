# Runner

This is a system for running R scripts on web.njit.edu. It consists of a bash
script to run Rscript taking input from the public_html/UPLOADS directory and
writing output to that same directory. For PHP configuration reasons, Runner
must exist within the public_html directory and can only write files to the
UPLOADS directory. PHP can only write files to the UPLOADS directory as well.

With this setup you can pass information to and from PHP and R via files.
