<?php
include 'stemmer/porter2stemmer.php';

class Keyword_Factory {

	public $char_exception = array(
			'!' => 'i',
			'$' => 's',
			'@' => 'a'
		);

	public $stopwords = array (
		0 => 'a',
		1 => 'about',
		2 => 'above',
		3 => 'across',
		4 => 'after',
		5 => 'afterwards',
		6 => 'again',
		7 => 'against',
		8 => 'all',
		9 => 'almost',
		10 => 'alone',
		11 => 'along',
		12 => 'already',
		13 => 'also',
		14 => 'although',
		15 => 'always',
		16 => 'am',
		17 => 'among',
		18 => 'amongst',
		19 => 'amoungst',
		20 => 'amount',
		21 => 'an',
		22 => 'and',
		23 => 'another',
		24 => 'any',
		25 => 'anyhow',
		26 => 'anyone',
		27 => 'anything',
		28 => 'anyway',
		29 => 'anywhere',
		30 => 'are',
		31 => 'around',
		32 => 'as',
		33 => 'at',
		34 => 'back',
		35 => 'be',
		36 => 'became',
		37 => 'because',
		38 => 'become',
		39 => 'becomes',
		40 => 'becoming',
		41 => 'been',
		42 => 'before',
		43 => 'beforehand',
		44 => 'behind',
		45 => 'being',
		46 => 'below',
		47 => 'beside',
		48 => 'besides',
		49 => 'between',
		50 => 'beyond',
		51 => 'bill',
		52 => 'both',
		53 => 'bottom',
		54 => 'but',
		55 => 'by',
		56 => 'call',
		57 => 'can',
		58 => 'cannot',
		59 => 'cant',
		60 => 'co',
		61 => 'computer',
		62 => 'con',
		63 => 'could',
		64 => 'couldnt',
		65 => 'cry',
		66 => 'de',
		67 => 'describe',
		68 => 'detail',
		69 => 'do',
		70 => 'done',
		71 => 'down',
		72 => 'due',
		73 => 'during',
		74 => 'each',
		75 => 'eg',
		76 => 'eight',
		77 => 'either',
		78 => 'eleven',
		79 => 'else',
		80 => 'elsewhere',
		81 => 'empty',
		82 => 'enough',
		83 => 'etc',
		84 => 'even',
		85 => 'ever',
		86 => 'every',
		87 => 'everyone',
		88 => 'everything',
		89 => 'everywhere',
		90 => 'except',
		91 => 'few',
		92 => 'fifteen',
		93 => 'fify',
		94 => 'fill',
		95 => 'find',
		96 => 'fire',
		97 => 'first',
		98 => 'five',
		99 => 'for',
		100 => 'former',
		101 => 'formerly',
		102 => 'forty',
		103 => 'found',
		104 => 'four',
		105 => 'from',
		106 => 'front',
		107 => 'full',
		108 => 'further',
		109 => 'get',
		110 => 'give',
		111 => 'go',
		112 => 'had',
		113 => 'has',
		114 => 'hasnt',
		115 => 'have',
		116 => 'he',
		117 => 'hence',
		118 => 'her',
		119 => 'here',
		120 => 'hereafter',
		121 => 'hereby',
		122 => 'herein',
		123 => 'hereupon',
		124 => 'hers',
		125 => 'herself',
		126 => 'him',
		127 => 'himself',
		128 => 'his',
		129 => 'how',
		130 => 'however',
		131 => 'hundred',
		132 => 'i',
		133 => 'ie',
		134 => 'if',
		135 => 'in',
		136 => 'inc',
		137 => 'indeed',
		138 => 'interest',
		139 => 'into',
		140 => 'is',
		141 => 'it',
		142 => 'its',
		143 => 'itself',
		144 => 'keep',
		145 => 'last',
		146 => 'latter',
		147 => 'latterly',
		148 => 'least',
		149 => 'less',
		150 => 'ltd',
		151 => 'made',
		152 => 'many',
		153 => 'may',
		154 => 'me',
		155 => 'meanwhile',
		156 => 'might',
		157 => 'mill',
		158 => 'mine',
		159 => 'more',
		160 => 'moreover',
		161 => 'most',
		162 => 'mostly',
		163 => 'move',
		164 => 'much',
		165 => 'must',
		166 => 'my',
		167 => 'myself',
		168 => 'name',
		169 => 'namely',
		170 => 'neither',
		171 => 'never',
		172 => 'nevertheless',
		173 => 'next',
		174 => 'nine',
		175 => 'no',
		176 => 'nobody',
		177 => 'none',
		178 => 'noone',
		179 => 'nor',
		180 => 'not',
		181 => 'nothing',
		182 => 'now',
		183 => 'nowhere',
		184 => 'of',
		185 => 'off',
		186 => 'often',
		187 => 'on',
		188 => 'once',
		189 => 'one',
		190 => 'only',
		191 => 'onto',
		192 => 'or',
		193 => 'other',
		194 => 'others',
		195 => 'otherwise',
		196 => 'our',
		197 => 'ours',
		198 => 'ourselves',
		199 => 'out',
		200 => 'over',
		201 => 'own',
		202 => 'part',
		203 => 'per',
		204 => 'perhaps',
		205 => 'please',
		206 => 'put',
		207 => 'rather',
		208 => 're',
		209 => 'same',
		210 => 'see',
		211 => 'seem',
		212 => 'seemed',
		213 => 'seeming',
		214 => 'seems',
		215 => 'serious',
		216 => 'several',
		217 => 'she',
		218 => 'should',
		219 => 'show',
		220 => 'side',
		221 => 'since',
		222 => 'sincere',
		223 => 'six',
		224 => 'sixty',
		225 => 'so',
		226 => 'some',
		227 => 'somehow',
		228 => 'someone',
		229 => 'something',
		230 => 'sometime',
		231 => 'sometimes',
		232 => 'somewhere',
		233 => 'still',
		234 => 'such',
		235 => 'system',
		236 => 'take',
		237 => 'ten',
		238 => 'than',
		239 => 'that',
		240 => 'the',
		241 => 'their',
		242 => 'them',
		243 => 'themselves',
		244 => 'then',
		245 => 'thence',
		246 => 'there',
		247 => 'thereafter',
		248 => 'thereby',
		249 => 'therefore',
		250 => 'therein',
		251 => 'thereupon',
		252 => 'these',
		253 => 'they',
		254 => 'thick',
		255 => 'thin',
		256 => 'third',
		257 => 'this',
		258 => 'those',
		259 => 'though',
		260 => 'three',
		261 => 'through',
		262 => 'throughout',
		263 => 'thru',
		264 => 'thus',
		265 => 'to',
		266 => 'together',
		267 => 'too',
		268 => 'top',
		269 => 'toward',
		270 => 'towards',
		271 => 'twelve',
		272 => 'twenty',
		273 => 'two',
		274 => 'un',
		275 => 'under',
		276 => 'until',
		277 => 'up',
		278 => 'upon',
		279 => 'us',
		280 => 'very',
		281 => 'via',
		282 => 'was',
		283 => 'we',
		284 => 'well',
		285 => 'were',
		286 => 'what',
		287 => 'whatever',
		288 => 'when',
		289 => 'whence',
		290 => 'whenever',
		291 => 'where',
		292 => 'whereafter',
		293 => 'whereas',
		294 => 'whereby',
		295 => 'wherein',
		296 => 'whereupon',
		297 => 'wherever',
		298 => 'whether',
		299 => 'which',
		300 => 'while',
		301 => 'whither',
		302 => 'who',
		303 => 'whoever',
		304 => 'whole',
		305 => 'whom',
		306 => 'whose',
		307 => 'why',
		308 => 'will',
		309 => 'with',
		310 => 'within',
		311 => 'without',
		312 => 'would',
		313 => 'yet',
		314 => 'you',
		315 => 'your',
		316 => 'yours',
		317 => 'yourself',
		318 => 'yourselves',
		319 => '-',
		320 => '--',
		321 => '.',
		322 => '..',
	);

	public function __construct() {
		$this->stemmer = new Porter2Stemmer;
	}

	public function normalize($string) {

		$string = htmlspecialchars($string, ENT_NOQUOTES, 'UTF-8', false);
		
		$string = htmlspecialchars_decode($string);

		$string = strip_tags($string);

		$string = preg_replace(array("#[-]#", "#[^0-9a-z\' ]#i", "#\s+#"), array('', ' ',' '), $string);

		$string = strtolower($string);

		return $string;
	}

	public function split_words($string) {
		$words = explode(' ', $string);
		
		$words = array_unique($words);

		return $words;
	}

	public function remove_stopwords($words = array()) {
		$filtered = array_diff($words, $this->stopwords);
		$wc = count($words);
		$fc = count($filtered);
		
		$quality = number_format((($fc / $wc) * 100), 2);
		echo "Keyword quality: $quality%\r\n";
		if($quality < 50) {
			return $words;
		}

		return $filtered;
	}

	public function stem($word) {
		$word = $this->stemmer->stem($word);

		return $word;
	}

	public function generate($string) {
		$string = $this->normalize($string);
		$words = $this->split_words($string);

		return $words;
	}

	/*
		title = 2
		artist = 1

		split strings
		remove stop words
		stem words
		weigh words
		insert hash string
	*/
}

?>