<?php

/**
 * @var string $defaultFont
 * @var string $defaultColor
 * @var string $titleColor
 */

$css = <<<HTML
<style>
	p {
		font-family: $defaultFont;
		font-size: 12pt;
		color: $defaultColor;
		padding-top: 0;
		padding-bottom: 0;
		margin-top: 0;
		margin-bottom: 0;
	}
	.centered {
		text-align: center;
	}
	.align-right {
		text-align: right;
	}
	.align-left {
		text-align: left;
	}
	.big {
		font-size: 16pt;
	}
	.title {
		font-size: 20pt;
		color: $titleColor;
		margin-top: 2em;
		margin-bottom: 2em;
	}
	.intitule-forma {
		font-size: 20pt;
		margin-bottom: 0;
	}
	.goals {
	}
</style>
HTML;
