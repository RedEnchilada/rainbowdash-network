<?php

if (!defined('STATUSNET')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class NoSwearPlugin extends Plugin {
	public $filterTo = array( // An array of strings to use as replacements for filtered words
		'banana',
		'apple',
		'mango',
		'cherry',
		'grape',
		'kiwi',
		'papaya',
		'FrankerZ',
		'Fluffle Puff',
		'batcave',
		'Potato Knishes'
	);

	private function _getWordlist($skipLinks = false) {
        $s = '\!\@\#\$\%\^\&\*';
        $wordlist = <<<ENDFILTER
bepis
h[o0$s]m[e3].?[s5][t7][u$s](c?k|c)
fight.club
d[i$s][c$s]?khead
\bdik
c[u$s]nt
\bcl[i1$s]t(oris)?\b
sh[i$s]te?(head)?
shat(?!ter)
vag(ina)?[sl]?\b
(m[ou]th(er|uh|a))?f[uoe$s][c$s]?k(?!ushima)
\bf[r$s]?e[c$s]?k\b
(da)?fuq
b[ie$s]a?[t$s][c$s]h
c[o$s][c$s]?k.?suc?k
nigg[^l]
\bcum\b
fag(g.t)?
p[ui$s][s$s]s
p[e3]?n[i1][5s]
ahole
d[o$s][u$s]che?( ?bag)?(?!ss)
b[aeiou$s]s?t[aeiou$s]rd
boner
fellat
jizz?
testicle
testes
\bw[ea$s]n[kg]
\bdbag
\btit[stzi](llat)?
\ban[ui]s
\banal\b
masturbat
fap
q[u$s][e$s][e$s]f
d[i$s][l$s][d$s]o
\btwat
ENDFILTER;
        $wordlist = '/'.($skipLinks ?
		'((^[^<]*)|(>[^<]*)|(<a[^>]*title="[^"]*))\\K' // Keep filter from affecting link hrefs
		: '').'((' . str_replace("\n", ")|(", str_replace("\r\n", "\n", $wordlist)) . '))/is';
		return $wordlist;
	}
	
    public function _filter($content, $skipLinks = false) {
		$wordlist = $this->_getWordlist($skipLinks);
		$choice = $this->filterTo;

		while(preg_match($wordlist, $content))
			$content = preg_replace_callback($wordlist, function($choice) use ($choice) {
				return $choice[array_rand($choice)];
			}, $content);
		
		// bleh
		$stripped_content = $content;
		if($skipLinks)
			$stripped_content = preg_replace('/((<.*?>)|(\\[\\/?([buist]|m[^\\]]*)\\]))/im', '', $stripped_content);
		
		while(preg_match($wordlist, $stripped_content)) {
			$stripped_content = preg_replace_callback($wordlist, function($choice) use ($choice) {
				return $choice[array_rand($choice)];
			}, $stripped_content);
			$content = $stripped_content;
		}
		
        return $content;
    }

    function onStartNoticeSave($notice) {
        $notice->content = $this->_filter($notice->content);
        $notice->rendered = $this->_filter($notice->rendered, true);
        return true;
    }
	
    function onSaveNewDirectMessage($notice) {
        $notice->content = $this->_filter($notice->content);
        $notice->rendered = $this->_filter($notice->rendered, true);
        return true;
    }

    function onStartRegistrationTry($action) {
        $action->args['bio'] = $this->_filter($action->trimmed('bio'));

        return true;
    }
	
	function onProcessWordfilter($text) {
		$text = $this->_filter($text);
		return true;
	}

    function onStartProfileSaveForm($action)
    {
        $action->args['bio'] = $this->_filter($action->trimmed('bio'));

        return true;
    }

    function onStartGroupSaveForm($action)
    {
        $action->args['description'] = $this->_filter($action->trimmed('description'));

        return true;
    }
}
?>
