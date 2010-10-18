@echo off
rem Deployment script for the EE components

rem NOTE: Run this script from the "StatehouseNews" directory

rem the deployment directories
set deploy_dir=C:\xampp\htdocs\ee\system
set module_dir=%deploy_dir%\modules\statehousenews
set models_dir=%deploy_dir%\modules\statehousenews\models
set views_dir=%deploy_dir%\modules\statehousenews\views
set helpers_dir=%deploy_dir%\modules\statehousenews\helpers
set language_dir=%deploy_dir%\language\english

rem make the module subdirectories
mkdir %module_dir%
mkdir %models_dir%
mkdir %views_dir%
mkdir %helpers_dir%

rem copy the module
cp modules/statehousenews/*.php %module_dir%

rem copy the models
cp modules/statehousenews/models/*.php %models_dir%

rem copy the views
cp modules/statehousenews/views/*.php %views_dir%

rem copy the helpers
cp modules/statehousenews/helpers/*.php %helpers_dir%

rem copy the language file
cp language/english/*.php %language_dir%

rem ------------------------------------------------------------------------------
rem @author Jerry D'Antonio
rem @see http://www.ideastream.org
rem @copyright Copyright (c) ideastream
rem @license http://www.opensource.org/licenses/mit-license.php
rem ------------------------------------------------------------------------------
rem Copyright (c) ideastream
rem
rem Permission is hereby granted, free of charge, to any person obtaining a copy
rem of this software and associated documentation files (the "Software"), to deal
rem in the Software without restriction, including without limitation the rights
rem to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
rem copies of the Software, and to permit persons to whom the Software is
rem furnished to do so, subject to the following conditions:
rem
rem The above copyright notice and this permission notice shall be included in
rem all copies or substantial portions of the Software.
rem
rem THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
rem IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
rem FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
rem AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
rem LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
rem OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
rem THE SOFTWARE.
rem ------------------------------------------------------------------------------
