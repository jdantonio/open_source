#!/usr/bin/env python

## @file
#
# Recurse through a directory and its subdirectories and call call the appropriate application for all files.

# package imports
import datetime
import os
import sys

################################################################################
# Operations Constants
################################################################################

# the command called for all files
# COMMAND = "lame -S --noreplaygain -V 8 --vbr-new --resample 44.1 -m m \"%s\" \"%s\""
COMMAND = "ffmpeg -i \"%s\" -y -b 1024000 -r 30 -s qvga -vcodec flv -ar 44100 \"%s\""

# the file name extension to process
OLD_EXT = ".mp4"

# the extension of the new file
NEW_EXT = ".flv"
################################################################################
# Main Method
################################################################################

def main():
    """
    Recurse through a directory and its subdirectories and call call the appropriate application for all files.
    """

    # get the current date and time
    now = datetime.datetime.today()

    # open a log file for writing
    try:
        sys.stdout = open(now.strftime("convert_log_%m%d%Y-%H%M%S.txt"), 'wt')
    except Exception, ex:
        print "Error opening the log file for writing."
        print ex
        sys.exit(1)

    # log operation start
    print "Begin processing at %s..." % now.strftime("%H:%M:%S on %m/%d/%Y")
    sys.stdout.flush()

    # create an empty directory list
    dirs = list()

    # get the directory name from the command line or assume current directory
    if len(sys.argv) == 1:
        dirs[:] = '.'
    else:
        dirs[:] = sys.argv[1:]

    # log directory list
    print "Converting ", dirs
    sys.stdout.flush()

    # loop through the directory list
    for dir in dirs:

        # walk the directory
        for root, dirs, files in os.walk(dir):
            print "Processing directory '%s'..." % root
            sys.stdout.flush()
            for name in files:
                process(root, name)

    # log completion
    now = datetime.datetime.today()
    print "Completed processing at %s..." % now.strftime("%H:%M:%S on %m/%d/%Y")
    sys.stdout.flush()

################################################################################
# Recursive Processing Function
################################################################################

def process(root, name):
    """
    Processing function. Recurses through the directory structure and calls
    Lame to convert all MP3 files to the desired sample rate.
    """

    # if the item is an MP3 file change the sample rate
    if name.lower().endswith(OLD_EXT):

        # set file names
        oldfile = os.path.join(root, name)
        newfile = os.path.join(root, name[:-len(OLD_EXT)] + NEW_EXT)

        # call lame
        try:
            #retcode = 0
            #print COMMAND % (oldfile, newfile)
            retcode = os.system(COMMAND % (oldfile, newfile))
            if retcode != 0:
                print "Execution was terminated by signal %d" % -retcode
            else:
                print "Successfully converted '%s'" % name
        except OSError, e:
            print "Execution failed: %s" % e
        sys.stdout.flush()

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
