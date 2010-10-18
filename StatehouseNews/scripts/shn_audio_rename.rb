#-------------------------------------------------------------------------------
# A script to rename all Statehouse News audio files.
# This script is only intended to be run once, during the migration of SHN into
# the ideastream ExpressionEngine instance on the live web servers.
#-------------------------------------------------------------------------------
require 'fileutils'

# get a list of all files in the current directory
basedir = '.'
destdir = './renamed/'
files = Dir.glob(['*.wav', '*.WAV'])

# create the destination directory
if ! File.exists?(destdir)
    FileUtils.mkdir(destdir)
end

# loop through all files
files.each do |file|

    # create the new file name
    new_path = file

    # remove all escaped characters
    new_path = new_path.gsub(/%\d\d/, '')

    # remove all special characters
    new_path = new_path.gsub(/\s/, ' ')
    new_path = new_path.gsub(/[^A-Za-z0-9\s\._-]/, '')
    
    # remove all spaces before dots
    new_path = new_path.gsub(/\s+\./, '.')

    # replace spaces with underscores
    new_path = new_path.gsub(/\s/, '_')

    # make lowercase
    new_path = new_path.downcase
    
    # add the destination path
    new_path = destdir + new_path

    # move the file
    p "Renaming '#{file}' to '#{new_path}'"
    FileUtils.cp(file, new_path)

end

#-------------------------------------------------------------------------------
# @author Jerry D'Antonio
# @see http://www.ideastream.org
# @copyright Copyright (c) ideastream
# @license http://www.opensource.org/licenses/mit-license.php
#-------------------------------------------------------------------------------
# Copyright (c) ideastream
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in
# all copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
# THE SOFTWARE.
#-------------------------------------------------------------------------------
