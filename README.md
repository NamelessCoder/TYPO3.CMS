TYPO3 CMS - Codename Gurpgork
=============================

This is a proof of concept fork. Here be dragons, caveat emptor, and so on. **DO NOT USE IN PRODUCTION!**


Original
--------

You can find the original repository at https://github.com/TYPO3/TYPO3.CMS - which also contains the original README.


What is this all about?
-----------------------

The vision for this fork is fairly simple:

* Reduce the number of system extensions to an absolute minimum
* Merge the system extensions required for a minimum operable state into the remaining system extensions
  - EXT:core (required)
  - EXT:backend (optional)
  - EXT:frontend (optional)
* Remove Extension Manager - composer only. The vision is that EM becomes a composer UI but for now, it's removed.
* Provide an out-of-the-box, zero-config solution to render Fluid page and content templates
* Provide the same out-of-the-box option to automatically use Fluid templates as new page templates or content types
* Introduce the concept of an "application delegate"
* Create a REST-capable API (read only to begin with)
* Use Symfony's Authentication component as replacement for old TYPO3 authentication service components
* Use Assetic as replacement for old PageRenderer JS/CSS handling

In summary: the goal is to make TYPO3 significantly smaller as a minimum distribution that still provides a perfectly
functional set of features which have an out-of-the-box zero-conf experience.

Please note that not all of the above is currently implemented - some are still TODOs.


Reducing the number of system extensions
----------------------------------------

A default TYPO3 installation (8.7) contains **49** system extensions. Of those, many are installed by default. Several
are required to be installed for TYPO3 to be operational. This concept drastically reduces these extensions to just
three extensions of which two are optional:

* EXT:core - the only required extension
* EXT:backend - optional, only required for sites that require backend access
* EXT:frontend - optional, only required for sites that require frontend output

Instead of having several system extensions responsible for various parts of the backend, the minimum required set of
extensions have been merged into EXT:core. Others such as EXT:tstemplate and EXT:filelist have been merged into
EXT:backend since they are only required there. Finally, EXT:frontend now provides output of pages and content using a
zero-conf integration which is activated when no other configuration exists. This zero-conf solution is described below.

More than 30 system extensions have simply been removed. This includes things like EXT:felogin, EXT:func, EXT:workspaces
and other opt-in type feature providers. The vision is that such extensions will be delivered as any other extension.

In terms of composer, this means:

* TYPO3 (minimal working state) can be installed by requiring `typo3/cms` which gives all three root packages
* CLI and environment can be installed by requiring `typo3/cms-core` (in case you make an application that creates
  its own frontend and backend output but uses TYPO3 as framework)
* Frontend output can be added by requiring `typo3/cms-frontend`
* The TYPO3 backend can be added by also requiring `typo3/cms-backend` (in case you create an application that replaces
  all frontend output but uses the TYPO3 backend for administration)

This brings TYPO3 to the point where *every* core extension is possible to ship individually. The "core" then consists
of a single extension and the scaffolding required by TYPO3 (which some day may be removed - in fact, such a removal is
made easier by the changes demonstrated in this fork). Even EXT:frontend and EXT:backend can be shipped separately from
the EXT:core+scaffolding repository. However: for ease of use, EXT:frontend and EXT:backend are still included in this
repository (but can be split automatically before Packagist release).

All other previous features of TYPO3 are now considered opt-in and must be installed by requiring the package name, e.g.
require `typo3/cms-workspaces` if you want to use the workspaces feature.

This system extension reduction is already a long term goal for TYPO3 - but this concept fork goes to the absolute
extreme by reducing that number to a mere three (two optional) *actual* system extensions.


Removal of the Extension Manager
--------------------------------

This fork intentionally removes the extension manager, even though there is no UI-based replacement for it (yet). The
only way to activate and deactivate extensions currently is through the CLI commands which have been moved to EXT:core.

The vision for the extension manager is:

* Remove the concept of "inactive" extensions; an extension is either installed or not, when the files are present,
  TYPO3 should assume the extension is installed.
* Provider a GUI using composer, see for example https://github.com/johnstevenson/composer-runtime

Both of which are not yet achieved. Once both are complete, the entire package management sub-logic can be removed from
the TYPO3 sources.

This relates to a planned change for TER to turn it into a pure metadata storage - at the current time it is now known
(to me) how deep this change will go, but it may end up meaning composer becomes the only way to install packages. The
vision above makes most sense if that happens: TER is then purely for searching for extensions, installing and resolving
dependencies is then done by composer.

*Disclaimer: my own personal opinion about whether or not there should even be a GUI-based module to install extensions
is that there should not be such a module. Therefore I will not be making this a priority - I will instead aim for the
first part of the vision: removing the notion about deactivated extensions and always assuming installed means active.*


Out-of-the-box, zero-config Fluid rendering
-------------------------------------------

The vision for EXT:frontend has to main sub-goals:

* Remove the need for configuration to begin rendering output (page and content templating)
* Provide a way to both override native content types and page template types and add new ones

This is achieved by splitting the PageRenderer into a base class, adding an interface and creating a new PageRenderer
implementation which renders Fluid templates and respects template path overlays. Templates are provided for an
*extremely* simple output (far, far simpler than EXT:fluid_styled_content - which is removed from this concept).

Overrides and new templates can be added by creating a new extension which contains templates and then declaring this
extension a "site" extension via metadata. Doing this automatically adds the configured (or convention fallback)
template paths as overlays for EXT:frontend.

To facilitate the automatic adding of new content types, an API has been created which adds objects containing type
definitions. The parts of this API are (each with both interface and at least one shipped implementation):

* ContentType
* ContentTypeRenderer
* ContentData
* RenderedObject

This is then tied together by an Application Delegate (described below) which is responsible for returning a valid
content type renderer, and a PageRenderer implementation. When a ContentTypeRenderer is asked to render a content type
it receives both the ContentType and ContentData, the latter is a container to store various metadata about the object.
It then returns a RenderedObject which contains the header and body (as rendered strings) along with a public method
to get the "content" which is then constructed from the header and body and any wrapping that is required. The
RenderedObject also contains the original ContentData so that any code which receives a RenderedObject automatically
knows about individual properties of the content record without having to re-fetch through SQL.

The new FluidPageRenderer is one such piece of code which receives RenderedObjects to use as content output.

Among other things this means that declaring a new content type always happens through the same API whether that new
content type is a Fluid template, an Extbase plugin, or something completely new like an embedded third party app.


The Application Delegate
------------------------

A new concept has been introduced to allow a class to decide which PageRenderer, which ContentTypes and which
ContentTypeRenderers get used. This makes that class able to switch every aspect of rendering: it allows replacing the
PageRenderer which means things like output format can be changed (to XML, JSON, PDF, whatever). It allows ContentType
definitions to be returned from a central place (to do things like change the available content types if a site is using
a specific Application Delegate. Finally, it allows returning different ContentTypeRenderers which can do things like
render arbitrary content types as any format.

Remember: the ContentTypeRenderer receives both the ContentType *and* the ContentData and can render any sub-set of the
data in any way it chooses, and it returns a RenderedObject which can also do things like wrap the header/body in a way
that works with the intended output.


The REST behavior
-----------------

By using the Application Delegate concept to create a special type of delegate which renders JSON instead of HTML, this
concept repository achieves a (read-only) REST+JSON delivery format containing the page metadata and resources, a list
of content elements and the metadata and pre-rendered content of each content element.

The RestApplicationDelegate as it is called, replaces page and content rendering with a JSON format. This allows you to
construct pages and content elements that can be rendered both as HTML or accessed by other applications to read page
and content data as JSON (with embedded rendered HTML). Note that it is up to the ApplicationDelegate whether or not
to actually render HTML or other compiled representations of objects - a pure data driven ApplicationDelegate could
for example choose to render nothing and instead return property values.

Furthermore: if a content element is an Extbase plugin and is capable of rendering JSON output, that output gets used.
This means you can provide templates in .json format for a plugin and instead of the HTML that normally gets embedded,
a valid JSON structure containing for example a news list or news detail properties as JSON. Of course the plugin still
reacts to things like filters and search parameters so a news list with search capabilities can accept query parameters
and return different JSON objects based on those.

The vision for this RestApplicationDelegate is:

* TODO: Let backend users define pages that can deliver JSON through REST and respect access restrictions
* TODO: Handle PUT, POST, DELETE (write) requests to store new content, update page properties and so on
* TODO: Create a CmisApplicationDelegate (possibly as extension) to enable TYPO3 to serve as CMIS repository

This goal is one of the more lofty ones and is very experimental - included mostly to demonstrate the power of the
Application Delegate concept to render any representation, not just HTML.


Use Symfony's Authentication component
--------------------------------------

This vision doesn't need a whole lot of description to be understood. Symfony contains an extensible authentication
component - this concept repository replaces the old TYPO3 EXT:sv, EXT:saltedpasswords and EXT:rsaauth with the Symfony
component and allows it to be configured with additional authentication providers.


Use Assetic instead of old JS/CSS include methods
-------------------------------------------------

As part of the change to split the PageRenderer and create an interface for it, a small wrapper API has been added which
allows replacing the asset handling. In this concept the PageRenderer delegates all asset inclusion to a small class
which sits between TYPO3 and any third-party asset handling logic that is desired. The default implementation is set to
use Assetic via an AsseticAssetBinding that implements the AssetBindingInterface.

The binding strategy is chosen to avoid making any final decisions about which asset engine a site must use.
