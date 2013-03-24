
 * @link     http://status.net/
 */
class MobileProfilePlugin extends WAP20Plugin
{
    public $DTD            = null;
    public $serveMobile    = false;
    public $reallyMobile   = false;
    public $mobileFeatures = array();

    function __construct($DTD='http://www.wapforum.org/DTD/xhtml-mobile10.dtd')
    {
        $this->DTD = $DTD;

        parent::__construct();
    }

    function onStartShowHTML($action)
    {
        // XXX: This should probably graduate to WAP20Plugin

        // If they are on the mobile site, serve them MP
        if ((common_config('site', 'mobileserver').'/'.
             common_config('site', 'path').'/' ==
            $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'])) {

            $this->serveMobile = true;
        } else if (isset($_COOKIE['MobileOverride'])) {
            // Cookie override is controlled by link at bottom.
            $this->serveMobile = (bool)$_COOKIE['MobileOverride'];
        } else {
            // If they like the WAP 2.0 mimetype, serve them MP
            // @fixme $type is undefined, making this if case useless and spewing errors.
            // What's the intent?
            //if (strstr('application/vnd.wap.xhtml+xml', $type) !== false) {
            //    $this->serveMobile = true;
            //} else {
                // If they are a mobile device that supports WAP 2.0,
                // serve them MP

                // XXX: Browser sniffing sucks

                // I really don't like going through this every page,
                // perhaps use $_SESSION or cookies

                // May be better to group the devices in terms of
                // low,mid,high-end

                // Or, detect the mobile devices based on their support for
                // MP 1.0, 1.1, or 1.2 may be ideal. Possible?

                $this->mobiledevices = array(
                    'alcatel',
                    'android',
                    'audiovox',
                    'au-mic,',
                    'avantgo',
                    'blackberry',
                    'blazer',
                    'cldc-',
                    'danger',
                    'epoc',
                    'ericsson',
                    'ericy',
                    'iphone',
                    'ipaq',
                    'ipod',
                    'j2me',
                    'lg',
                    'maemo',
                    'midp-',
                    'mobile',
                    'mot',
                    'netfront',
                    'nitro',
                    'opera mini',
                    'palm',
                    'palmsource',
                    'panasonic',
                    'philips',
                    'pocketpc',
                    'portalmmm',
                    'rover',
                    'samsung',
                    'sanyo',
                    'series60',
                    'sharp',
                    'sie-',
                    'smartphone',
                    'sony',
                    'symbian',
                    'up.browser',
                    'up.link',
                    'up.link',
                    'vodafone',
                    'wap1',
                    'wap2',
                    'webos',
                    'windows ce'
                );

                $blacklist = array(
                    'ipad', // Larger screen handles the full theme fairly well.
               	    'nokia'
		 );

                $httpuseragent = strtolower($_SERVER['HTTP_USER_AGENT']);

                foreach ($blacklist as $md) {
                    if (strstr($httpuseragent, $md) !== false) {
                        $this->serveMobile = false;
                        return true;
                    }
                }

                foreach ($this->mobiledevices as $md) {
                    if (strstr($httpuseragent, $md) !== false) {
                        $this->setMobileFeatures($httpuseragent);

                        $this->serveMobile = true;
                        $this->reallyMobile = true;
                        break;
                    }
                }
            //}

            // If they are okay with MP, and the site has a mobile server,
            // redirect there
            if ($this->serveMobile &&
                common_config('site', 'mobileserver') !== false &&
                (common_config('site', 'mobileserver') !=
                    common_config('site', 'server'))) {

                // FIXME: Redirect to equivalent page on mobile site instead
                common_redirect($this->_common_path(''), 302);
            }
        }

        //if (!$this->serveMobile) {
            return true;
        //}

        // @fixme $type is undefined, making this if case useless and spewing errors.
        // What's the intent?
        //if (!$type) {
            $httpaccept = isset($_SERVER['HTTP_ACCEPT']) ?
              $_SERVER['HTTP_ACCEPT'] : null;

            $cp = common_accept_to_prefs($httpaccept);
            $sp = common_accept_to_prefs(PAGE_TYPE_PREFS_MOBILEPROFILE);

            $type = common_negotiate_type($cp, $sp);

            if (!$type) {
                // TRANS: Client exception thrown when requesting a not supported media type.
                throw new ClientException(_m('This page is not available in a '.
                                            'media type you accept.'), 406);
            }
        //}

        header('Content-Type: '.$type);

        if ($this->reallyMobile) {
            $action->extraHeaders();
            if (preg_match("/.*\/.*xml/", $type)) {
                // Required for XML documents
                $action->xw->startDocument('1.0', 'UTF-8');
            }
            $action->xw->writeDTD('html',
                '-//WAPFORUM//DTD XHTML Mobile 1.0//EN',
                $this->DTD);


            $language = $action->getLanguage();

            $action->elementStart('html', array('xmlns' => 'http://www.w3.org/1999/xhtml',
                                            'xml:lang' => $language));

            return false;

        } else {
        return true;
        }

    }

    function setMobileFeatures($useragent)
    {
        $mobiledeviceInputFileType = array(
            'nokia'
        );

        $this->mobileFeatures['inputfiletype'] = false;

        foreach ($mobiledeviceInputFileType as $md) {
            if (strstr($useragent, $md) !== false) {
                $this->mobileFeatures['inputfiletype'] = true;
                break;
            }
        }
    }

    function onStartShowStatusNetStyles($action)
    {
        if (!$this->serveMobile) {
            return true;
        }

        $action->primaryCssLink();

        $action->cssLink($this->path('mp-screen.css'),null,'screen');
        if (file_exists(Theme::file('css/mp-screen.css'))) {
            $action->cssLink('css/mp-screen.css', null, 'screen');
        }

        $action->cssLink($this->path('mp-handheld.css'),null,'handheld');
        if (file_exists(Theme::file('css/mp-handheld.css'))) {
            $action->cssLink('css/mp-handheld.css', null, 'handheld');
        }

        // Allow other plugins to load their styles.
        Event::handle('EndShowStatusNetStyles', array($action));
        Event::handle('EndShowLaconicaStyles', array($action));

        return false;
    }

    function onStartShowUAStyles($action) {
        if (!$this->serveMobile) {
            return true;
        }

        return false;
    }

    function onStartShowHeader($action)
    {
        if (!$this->serveMobile) {
            return true;
        }

        $action->elementStart('div', array('id' => 'header'));
        $this->_showLogo($action);
        $this->_showPrimaryNav($action);
        if (common_logged_in()) {
            $action->showNoticeForm();
        }
        $action->elementEnd('div');

        return false;
    }

    function onEndShowHeadElements($action) {
        if (!$this->serveMobile) {
            return true;
        }

        // Fix the zoom
        $action->element('meta', array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'));
        $action->element('meta', array('name' => 'HandheldFriendly', 'content' => 'true'));

        return true;
    }

    function _showLogo($action)
    {
        $action->elementStart('address', 'vcard');
        if (common_config('singleuser', 'enabled')) {
            $user = User::singleUser();
            $url = common_local_url('showstream', array('nickname' => $user->nickname));
        } else {
            $url = common_local_url('public');
        }

        $action->elementStart('a', array('class' => 'url home bookmark',
                                         'href' => $url));

        if (common_config('site', 'mobilelogo') ||
            file_exists(Theme::file('logo.png')) ||
            file_exists(Theme::file('mobilelogo.png'))) {

            $action->element('img', array('class' => 'photo',
                'src' => (common_config('site', 'mobilelogo')) ? common_config('site', 'mobilelogo') :
                            ((file_exists(Theme::file('mobilelogo.png'))) ? (Theme::path('mobilelogo.png')) : Theme::path('logo.png')),
                'alt' => common_config('site', 'name')));
        }
        $action->element('span', array('class' => 'fn org'), common_config('site', 'name'));
        $action->elementEnd('a');
        $action->elementEnd('address');
    }

    function _showPrimaryNav($action)
    {
        $user    = common_current_user();
		$class = $user ? 'loggedin' : 'loggedout';
        $action->elementStart('ul', array('id' => 'site_nav_global_primary'));
		
		$action->element('span', array('id'=>'nav_dummy'));

		// TRANS: Tooltip for main menu option "Rules".
		$action->menuItem(common_local_url('doc', array('title' => 'rules')),
						_m('MENU', 'Rules'));
        if ($user) {
            $action->menuItem(common_local_url('all', array('nickname' => $user->nickname)),
                            _m('Personal'));

                // TRANS: Tooltip for main menu option "Account".
                $tooltip = _m('TOOLTIP', 'Your incoming messages');
                $action->menuItem(common_local_url('inbox', array('nickname' => $user->nickname)),
                                // TRANS: Main menu option when logged in for access to user settings.
                                _('Inbox'), $tooltip, false, 'nav_dmcounter');
								
            $action->menuItem(common_local_url('profilesettings'),
                            _m('Account'));
            /*$action->menuItem(common_local_url('oauthconnectionssettings'),
                                _m('Connect'));*/
            if ($user->hasRight(Right::CONFIGURESITE)) {
                $action->menuItem(common_local_url('siteadminpanel'),
                                _m('Admin'), null, false, 'nav_admin');
            }
            /*if (common_config('invite', 'enabled')) {
                $action->menuItem(common_local_url('invite'),
                                _m('Invite'));
            }*/
            $action->menuItem(common_local_url('peoplesearch'),
                            _m('Search'));
            $action->menuItem(common_local_url('logout'),
                            _m('Logout'));
        } else {
			if (!common_config('site', 'private')) {
				$action->menuItem(common_local_url('peoplesearch'),
								_m('Search'));
			}
            if (!common_config('site', 'closed')) {
                $action->menuItem(common_local_url('register'),
                                _m('Register'));
            }
            $action->menuItem(common_local_url('login'),
                            _m('Login'));
        }
		$action->element('div', array('style' => 'clear: both;width: 1px;height: 1px'));
        $action->elementEnd('ul');
    }

    function onStartShowAside($action)
    {
        if ($this->serveMobile) {
            return false;
        }
    }

    function onStartShowLocalNavBlock($action)
    {
        if ($this->serveMobile) {
            // @todo FIXME: "Show Navigation" / "Hide Navigation" needs i18n
            $action->element('a', array('href' => '#', 'id' => 'navtoggle'), 'Show Navigation');
        return true;
        }
    }

    function onEndShowScripts($action)
    {
        // @todo FIXME: "Show Navigation" / "Hide Navigation" needs i18n
        $action->inlineScript('
            $(function() {
                $("#mobile-toggle-disable").click(function() {
                    $.cookie("MobileOverride", "0", {path: "/"});
                    window.location.reload();
                    return false;
                });
                $("#mobile-toggle-enable").click(function() {
                    $.cookie("MobileOverride", "1", {path: "/"});
                    window.location.reload();
                    return false;
                });
                $("#navtoggle").click(function () {
                          $("#site_nav_local_views").fadeToggle();
                          var text = $("#navtoggle").text();
                          $("#navtoggle").text(
                          text == "Show Navigation" ? "Hide Navigation" : "Show Navigation");
                });
            });'
        );

        if ($this->serveMobile) {
            $action->inlineScript('
                $(function() {
        	        $(".checkbox-wrapper").unbind("click");
                });'
            );
        }


    }


    function onEndShowInsideFooter($action)
    {
        if ($this->serveMobile) {
            // TRANS: Link to switch site layout from mobile to desktop mode. Appears at very bottom of page.
            $linkText = _m('Switch to desktop site layout.');
            $key = 'mobile-toggle-disable';
        } else {
            // TRANS: Link to switch site layout from desktop to mobile mode. Appears at very bottom of page.
            $linkText = _m('Switch to mobile site layout.');
            $key = 'mobile-toggle-enable';
        }
        $action->elementStart('p');
        $action->element('a', array('href' => '#', 'id' => $key), $linkText);
        $action->elementEnd('p');
        return true;
    }

    function _common_path($relative, $ssl=false)
    {
        $pathpart = (common_config('site', 'path')) ? common_config('site', 'path')."/" : '';

        if (($ssl && (common_config('site', 'ssl') === 'sometimes'))
            || common_config('site', 'ssl') === 'always') {
            $proto = 'https';
            if (is_string(common_config('site', 'sslserver')) &&
                mb_strlen(common_config('site', 'sslserver')) > 0) {
                $serverpart = common_config('site', 'sslserver');
            } else {
                $serverpart = common_config('site', 'mobileserver');
            }
        } else {
            $proto      = 'http';
            $serverpart = common_config('site', 'mobileserver');
        }

        return $proto.'://'.$serverpart.'/'.$pathpart.$relative;
    }

    function onPluginVersion(&$versions)
    {
        $versions[] = array('name' => 'MobileProfile',
                            'version' => STATUSNET_VERSION,
                            'author' => 'Sarven Capadisli',
                            'homepage' => 'http://status.net/wiki/Plugin:MobileProfile',
                            'rawdescription' =>
                            // TRANS: Plugin description.
                            _m('XHTML MobileProfile output for supporting user agents.'));
        return true;
    }
}
