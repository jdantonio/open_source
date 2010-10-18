#!/usr/bin/env python

## @file
#
# Create backup of one or more given files.

# package imports
import datetime
import shutil
import sys

# get the file names from the command line else die
if len(sys.argv) == 1 :
    print 'No file given, exiting.'
    sys.exit(1)

# get backup directory location if provided
file_path = ''
backup_path = None
if len(sys.argv) >= 4 and sys.argv[1] == '-d':
    backup_path = sys.argv[2]

# create a date string
date_str = datetime.datetime.today().strftime("_%m%d%Y-%H%M%S")

# get the list of files to backup
if backup_path == None :
    file_list = sys.argv[1:]
else :
    file_list = sys.argv[3:]

# loop through the list of files
for f in file_list :

    # separate the path from the file name
    index = f.rfind("\\")
    if index == -1 :
        index = f.rfind('/')

    # set the backup path
    if index != -1 :
        file_path = f[0:index]
        f = f[index:len(f)]
    
    # separate the extension from the file name
    index = f.rfind('.')
    
    # create the backup file name
    if index != -1 :
        f2 = f[0:index] + date_str + f[index:]
    else :
        f2 = f + date_str

    # set the paths
    if backup_path == None :
        backup_path = file_path
    f = file_path + f
    f2 = backup_path + f2

    # copy the file to the backup
    try :
        shutil.copyfile(f, f2)
        print "Successfully copied '%s' to '%s'" % (f, f2)
    except Exception, ex :
        print "Failed to copy '%s' (%s)" % (f, ex)
    
    # clear path information
    file_path = ''
    backup_path = None

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
