# Runner

This is a system for running R scripts on web.njit.edu. It consists of a bash
script to run Rscript taking input from the public_html/UPLOADS directory and
returning output to a custom directory. For PHP configuration reasons, Runner
must exist within the public_html directory.

With this setup you can pass information to and from PHP and R via files.
