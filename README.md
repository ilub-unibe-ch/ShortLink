#ShortLink Plugin Installation

## Requirements

1. ilias 5.0 or higher
2. unibe /Customizing/global
3. CtrlMainMenu Plugin

### CtrlMainMenu Plugin Installation

Clone the CtrlMinMenu plugin into Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu and
checkout the required version you need.

	git clone https://github.com/iLUB/CtrlMainMenu.git

Go to Administration -> Plugins, apply the updates and activate the CtrlMainMenu plugin.

Configure the plugin, remove the unwanted entries like 'search', 'status' and 'settings'.

## Installation of the ShortLink Plugin

Clone the ShortLink repository to Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ and checkout the
required version you need.
	
	git clone http://ilublx3.unibe.ch/kolonko/ShortLink.git

Go to Administration -> Plugins, apply the updates and activate the ShortLink plugin.

## Setup the link to the ShortLink GUI

Got to Administration -> Plugins -> CtrlMainMenu -> Configure

1. Add Entry
2. Select Type 'ilCtrl'
3. Click "Select Type"
4. Fill in the fields required
5. Add into filed GUI Classes (WITHOUT WHITESPACES): "ilUIPluginRouterGUI,ilShortLinkGUI"
6. Click "Save and close"

There is a new ShortLink entry visible at the top menu of ILIAS.

### VHost Configuration

It is important that the rewrite_mod is enabled (should be already the case).

Add the rewrite to the end of the rewrite rules in the current VHost.conf

RewriteRule \^/link/([a-zA-Z0-9-]+)?$ https://ilias-next.unibe.ch/Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink/redirect.php?shortlink=$1 [L]

If you work on localhost add:

RewriteRule \^/link/([a-zA-Z0-9-]+)?$ http://localhost:8081/Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/ShortLink/redirect.php?shortlink=$1 [L]