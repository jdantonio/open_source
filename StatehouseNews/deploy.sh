# Deployment script for the EE components

# NOTE: Run this script from the "StatehouseNews" directory

# the deployment directories
export deploy_dir=/var/www/ExpressionEngine/qwerty/
#export deploy_dir=/Applications/MAMP/htdocs/ee/system
export module_dir=$deploy_dir/modules/statehousenews
export models_dir=$deploy_dir/modules/statehousenews/models
export views_dir=$deploy_dir/modules/statehousenews/views
export helpers_dir=$deploy_dir/modules/statehousenews/helpers
export language_dir=$deploy_dir/language/english

# make the module subdirectories
mkdir $module_dir
mkdir $models_dir
mkdir $views_dir
mkdir $helpers_dir

# copy the module
cp modules/statehousenews/*.php $module_dir

# copy the models
cp modules/statehousenews/models/*.php $models_dir

# copy the views
cp modules/statehousenews/views/*.php $views_dir

# copy the helpers
cp modules/statehousenews/helpers/*.php $helpers_dir

# copy the language file
cp language/english/*.php $language_dir

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
