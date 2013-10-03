<?php

if (!defined('STATUSNET')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

class NoSwearPlugin extends Plugin
{
	private function getWordlist() {
        $s = '\!\@\#\$\%\^\&\*';
        $wordlist = <<<HERE
d[i$s][c$s]?khead
\bdik
[^s]c[u$s]nt
\bc[u$s]nt
s[h$s][i$s]t(?!su|a|o)e?(head)?
\bvag(ina)?\b
(m[ou]th(er|uh|a))?f[r$s]?[uo$s][c$s]?k
\bf[r$s]?e[c$s]?k\b
(da)?fuq
b[ie$s]a?[t$s][c$s][h$s]
c[o$s][c$s]?k.?suc?k
nigg[^l]
\bcum\b
fag(g.t)?
p[ui$s][s$s]s
p[e3]?n[i1][5s]
ahole
d[o$s]?[u$s]che?( ?bag)?(?!ss)
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
HERE;
        $wordlist = '/((' . str_replace("\n", ")|(", str_replace("\r\n", "\n", $wordlist)) . '))/i';
		return $wordlist;
	}
	
    public function _filter($content) {
		$wordlist = $this->getWordlist();
        $choice = array(
            'bananas',
            'apples',
            'mangoes',
            'cherries',
            'grapes',
            'kiwi',
            'dole',
            'murdock',
            'papayas',
        );

        $content = preg_replace_callback($wordlist, function($choice) use ($choice) {
            return $choice[array_rand($choice)];
        }, $content);
        return $content;
    }

    function onStartNoticeSave($notice) {
        $notice->content = $this->_filter($notice->content);
        $notice->rendered = $this->_filter($notice->rendered);
        return true;
    }
	
    function onSaveNewDirectMessage($notice) {
        $notice->content = $this->_filter($notice->content);
        $notice->rendered = $this->_filter($notice->rendered);
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
}
?>
