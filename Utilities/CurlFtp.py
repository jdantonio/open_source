#!/usr/bin/env python

## @file
#
# cURL wrapper for FTP operations.
#
# @see http://pycurl.sourceforge.net/

# package imports
import os

import pycurl

################################################################################
# Global Constants
################################################################################

NO_ERROR = 0
UNKNOWN_ERROR = -1
EMPTY_STRING = ''

################################################################################
# Class Definition
################################################################################

class CurlFtp(object) :
    """
    cURL wrapper for FTP operations.
    """

    Protocol = EMPTY_STRING
    Host = EMPTY_STRING
    Port = EMPTY_STRING
    User = EMPTY_STRING
    Passwd = EMPTY_STRING
    UploadPath = EMPTY_STRING
    Timeout = EMPTY_STRING

    _curl = None
    _errstr = EMPTY_STRING
    _errno = NO_ERROR

    def __init__(self, protocol, host, port, user, passwd, path = 'upload', timeout = 10) :
        """
        Constructor
        """

        self.Protocol = protocol
        self.Host = host
        self.Port = port
        self.User = user
        self.Passwd = passwd
        self.UploadPath = path
        self.Timeout = timeout

        if not self.Protocol.endswith('://') :
            self.Protocol += '://'

        self.reset();


    def __del__(self) :
        """
        Destructor
        """
        if self._curl :
            self._curl.close()


    def getErrstr(self) :
        """
        The error message returned by the last cURL operation.
        """
        return self._errstr
    Errstr = property(getErrstr, 'The error message returned by the last cURL operation.')


    def getErrno(self) :
        """
        The error number returned by the last cURL operation.
        """
        return self._errno
    Errno = property(getErrno, 'The error number returned by the last cURL operation.')


    def getCurlVersion(self) :
        """
        Returns run-time libcurl version info.
        """
        return pycurl.version
    CurlVersion = property(getCurlVersion, 'Returns run-time libcurl version info.')


    def __str__(self) :
        """
        Convert this object to a string as one line incorporating Errno and Errstr.
        """
        return 'Error Number: %d, Error Message: \'%s\'' % (self.Errno, self.Errstr)

    def _set_error(self, ex) :
        self._errno = ex[0]
        self._errstr = ex[1]


    def _perform(self) :
        """
        Perform a cURL operation after all options have been set
        """

        try :
            # connect to the ftp server
            self._errno = self._curl.perform()
        except Exception, ex :
            self._set_error(ex)
        else :
            self._errstr = self._curl.errstr()
            if self._errno == None :
                self._errno = NO_ERROR

        # return errno
        return self._errno


    def reset(self) :
        """
        Reset the cURL session and set common options. Creates a new session if
        one does not exists.
        """

        # close an open session if it exists
        if self._curl :
            self._curl.close()

        # reset the error variables
        self._errstr = EMPTY_STRING;
        self._errno = NO_ERROR;

        # initialize a new session
        self._curl = pycurl.Curl()

        # set common options on success
        if self._curl :
            self._curl.setopt(pycurl.USERPWD, self.User + ':' + self.Passwd)
            #self._curl.setopt(pycurl.RETURNTRANSFER, True)
            self._curl.setopt(pycurl.SSL_VERIFYPEER, False)
            self._curl.setopt(pycurl.SSL_VERIFYHOST, False)
            self._curl.setopt(pycurl.CONNECTTIMEOUT, self.Timeout)
            self._curl.setopt(pycurl.FTP_USE_EPSV, True)
            #self._curl.setopt(pycurl.ERRORBUFFER, True)

        # return a boolean
        if self._curl :
            return True
        else :
            return False


    def test(self) :
        """
        Test the FTP connection.
        """

        # set the URL
        self._curl.setopt(pycurl.URL, self.Protocol + self.Host);

        # perform the operation
        self._perform()

        # reset the cURL options for the next command
        self.reset()

        return self.Errno


    def upload(self, file_name, file_path = EMPTY_STRING) :
        """
        Upload a file.
        """

        # declare variables
        fname = file_path + '/' + file_name
        fsize = 0
        infile = None

        try :

            # get the file size
            fsize = os.path.getsize(fname)

            # open the file for upload
            infile = open(fname, 'rU')

            # set the full upload path
            upload = self.Host + '/' + self.UploadPath + '/' + file_name
            upload = upload.replace('//', '/')

            # set the additional cURL options
            self._curl.setopt(pycurl.URL, self.Protocol + upload)
            self._curl.setopt(pycurl.TRANSFERTEXT, True)
            self._curl.setopt(pycurl.UPLOAD, True)
            self._curl.setopt(pycurl.READDATA, infile)
            self._curl.setopt(pycurl.INFILESIZE, fsize)

            # perform the operation
            self._perform()

            # close the input file file
            if isinstance(infile, file) :
                infile.close()

        except Exception, ex:
            self._set_error(ex)

        # reset the cURL options for the next command
        self.reset()

        return self.Errno


    def dir_list(self, file_path = None) :
        """
        Get a directory listing from the FTP server. If no path is given then the
        default upload directory is assumed. Use Test to get a directory listing
        of the FTP root directory.
        """

        # set the file path
        if file_path :
            file_path =  '/' + file_path + '/'
            file_path = file_path.replace('//', '/')
        else :
            file_path = self.UploadPath

        # set the additional cURL options
        self._curl.setopt(pycurl.URL, self.Protocol + self.Host + file_path)

        # perform the operation
        self._perform()

        # reset the cURL options for the next command
        self.reset()

        return self.Errno

################################################################################
# Entry Point Code
################################################################################

if __name__ == '__main__':

    ftp = CurlFtp('ftps', 'wviz.ftp.targetsite.com', '990', 'mflippen', '7gAVM8gC')
    print ftp.getCurlVersion()
    ftp.dir_list('/upload/')
    print 'cURL Result: (%s) %s' % (ftp.Errno, ftp.Errstr)

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
