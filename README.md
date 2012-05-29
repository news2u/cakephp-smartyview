cakephp-smartyview
==================

Smarty View class and helpers plugin for CakePHP 2.0 and later.
Works with Smarty version 2.6 and 3.1 both.

## Installation

In plugin directory of your app:

    git git://github.com/news2u/cakephp-smartyview.git SmartyView

## How to use

Install Smarty to Vendor directory.

Set $viewClass in AppController class;

    class AppController extends Controller {   

        ...      
    
        public $viewClass = 'SmartyView.Smarty';
        public $helpers = array(
            'SmartyView.SmartyHtml', 
            'SmartyView.SmartyForm',
            'SmartyView.SmartySession',
            'SmartyView.SmartyJavascript', 
            'Html', 'Session'
        );

        ...
    }
    
And put Smarty Template in View directory instead of .ctp file.

## Reference

SmartyView original code are written by tclineks and icedcheese:

* http://bakery.cakephp.org/articles/tclineks/2006/10/27/how-to-use-smarty-with-cake-smartyview
* http://bakery.cakephp.org/articles/icedcheese/2008/01/14/smarty-view-for-1-2

Original Helpers are also written by tclineks

* http://bakery.cakephp.org/articles/view/138 (currentry page not found)

