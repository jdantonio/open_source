#!/usr/bin/env python

## @file
#
# Remove old files from an archive directory. Intended to be run as a cron job.
#
# @link http://docs.python.org/lib/module-getopt.html

# package imports
import datetime
import os
import sys
import getopt

from optparse import OptionParser

################################################################################
# Main Method
################################################################################

def main():
    """
    Remove old files from an archive directory. Intended to be run as a cron job.
    """

    ############################################################################
    # Parse the command-line

    # setup the command-line parser
    parser = OptionParser(version="%prog $Revision: 100 $")

    # option for the number of days worth of files to keep
    parser.add_option("-d", "--days", type="int", action="store", dest="days",
                      default=0, help="Delete files older than this number of days")

    # option for the path to clean
    parser.add_option("-p", "--path", type="string", action="store", dest="path",
                      default=".", help="Full path of the directory to be cleaned")

    # option for the file extension of files to delete
    parser.add_option("-e", "--ext", type="string", action="store", dest="ext",
                      default="*", help="Only delete files with the given extension")

    # option for whether or not to recurse through subdirectories
    parser.add_option("-r", "--recurse", action="store_true", dest="recurse",
                      default=False, help="Recurse through the directory tree")

    # option for whether or not to exclude a specified directory
    parser.add_option("-x", "--exclude", action="append", dest="exclude",
                      help="Exclude the specified directory (may be used more than once), only works when -r is present")

    # get the command-line parameters
    (options, args) = parser.parse_args()

    # check the command-line options
    if options.days == 0 or options.path == ".":
        parser.error("Missing or invalid parameters.\nUse --help to get more information.")

    ############################################################################
    # Process the given directory

    # calculate the cutoff date
    now = datetime.datetime.today()
    diff = datetime.timedelta(int(options.days), 0, 0, 0, 0, 0, 0)
    cutoff = now - diff

    # tell the user what we are doing
    print "Directory: '%s'" % options.path
    print "Cutoff Date: %s" % cutoff.strftime("%m/%d/%Y")
    print "File Extension: '.%s'" % options.ext
    print "Recursive: %s" % options.recurse
    print "Exclude: %s" % options.exclude

    # perform the cleaning
    if options.recurse:

        # walk the directory tree
        for root, dirs, files in os.walk(options.path):
            ok = True
            for x in options.exclude:
                if root.lower().find(x.lower()) == 0:
                    ok = False
                    break
            if ok:
                print "Processing directory '%s'..." % root
                for name in files:
                    process(root, name, cutoff, options.ext)
            else:
                print "Excluding directory '%s'..." % root

    else:

        # clean only the root directory
        for file in os.listdir(options.path):
            process(options.path, file, cutoff, options.ext)

def process(root, name, cutoff, ext):
    """
    Processing method. Looks at all files in the given directory and deletes
    all files with the given extension that are older than the given number of
    days.
    """

    fullname = os.path.join(root, name)

    st_atime = datetime.datetime.fromtimestamp(os.stat((fullname))[7])
    st_mtime = datetime.datetime.fromtimestamp(os.stat((fullname))[8])
    st_ctime = datetime.datetime.fromtimestamp(os.stat((fullname))[9])

    if os.name == 'posix':
        stat_time = st_mtime
    elif os.name == 'nt':
        stat_time = st_ctime
    else:
        stat_time = st_ctime

    # check the extension and modification date
    if (fullname.endswith(ext) or ext == '*') and cutoff > stat_time:
 
        # delete the file
        try:
            print "Deleting '%s'" % fullname
            os.remove(fullname)
        except OSError, e:
            print "Deletion of '%s' failed: %s" % (fullname, e)
    else:
        print "Skipping '%s'" % fullname

################################################################################
# Entry Point Code
################################################################################

if __name__ == "__main__":
    main()

################################################################################
## @author Jerry D'Antonio
## @see http://www.ideastream.org
## @copyright Copyright (c) ideastream
## @license http://www.opensource.org/licenses/mit-license.php
################################################################################
## Copyright (c) ideastream
##
## Permission is hereby granted, free of charge, to any person obtaining a copy
## of this software and associated documentation files (the "Software"), to deal
## in the Software without restriction, including without limitation the rights
## to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
## copies of the Software, and to permit persons to whom the Software is
## furnished to do so, subject to the following conditions:
##
## The above copyright notice and this permission notice shall be included in
## all copies or substantial portions of the Software.
##
## THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
## IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
## FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
## AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
## LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
## OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
## THE SOFTWARE.
################################################################################
