<?php

$myModelInitDate = NULL;
$myModelValidDate = NULL;

define('HOUR_INCREMENT', '2');
$myDateFormat = 'l j F Y H:i';
$myDateFormat2 = 'D j/m/y H\hi e';
$myDateFormatI18n = '%a %e/%m/%y %k\h%M e';
$tzUTC = new DateTimeZone('UTC');
$tzParis = new DateTimeZone('Europe/Paris');
$myDateFormatter = new IntlDateFormatter('fr_FR',IntlDateFormatter::LONG,
        IntlDateFormatter::NONE,
        'Europe/Paris',
        IntlDateFormatter::GREGORIAN,
	"EEEE dd/MM/yy HH'h'" );
$myDateFormatterTz = new IntlDateFormatter('fr_FR',IntlDateFormatter::LONG,
        IntlDateFormatter::NONE,
        'Europe/Paris',
        IntlDateFormatter::GREGORIAN,
	"VVVV" );

$myTimeStamp = new DateTime("now", $tzParis);
$myModelRun01Utc = new DateTime("now", $tzParis);
$myModelRun02Utc = new DateTime("now", $tzParis);

$myTimeStamp->setTimezone($tzUTC);

// Horaires UTC de mises à jour du modèle WW3 Lamma Rete
// cf http://www.lamma.rete.toscana.it/mare/modelli/ww3-info-sul-modello
$myModelRun01Utc = $myModelRun01Utc->setTime(7,30);
$myModelRun02Utc = $myModelRun02Utc->setTime(21,30);

if ( $myTimeStamp <= $myModelRun01Utc ) {
	// avant 7h30 UTC: init = J-1 à 12h UTC
	$myModelInitDate = new DateTime("now", $tzUTC);
	$myModelInitDate = $myModelInitDate->sub(new DateInterval('P1D'));
	$myModelInitDate = $myModelInitDate->setTime(12, 00);
	$myLoopInit = "9";

} elseif ($myTimeStamp >= $myModelRun02Utc ) {
	//  après 21h30 UTC: init = J à 12h UTC
	$myModelInitDate = new DateTime("now", $tzUTC);
	$myModelInitDate = $myModelInitDate->setTime(12, 00);
	$myLoopInit = "9";

} else {
	// entre 7h30 et 21h30 UTC: init = J à 00h UTC
	$myModelInitDate = new DateTime("now", $tzUTC);
	$myModelInitDate = $myModelInitDate->setTime(00, 00);
	$myLoopInit = "7";
}
$myModelInitDate->setTimezone($tzParis);
?>

<!DOCTYPE html> 
<html lang="fr">
<head>
<title>Prévisions Météo Bouches de Bonifacio / Archipel Maddalena de LammaRete adapté par Matthieu 2T</title>
<meta charset="UTF-8" >
<style type="text/css">
.tzSmall {font-size: small; font-weight:normal;}
</style>
</head>
<body>
<h1>Prévisions météo pour mes stages Glénans Bouches de Bonif / Archipel Maddalena</h1>
<p><a href="#metaInfo">Pourquoi cette page ?</a></p>

<?php
# Images directly from LammaRete (without crop nor optimisation)
$myUrlStub='http://www.lamma.rete.toscana.it/models/ventoemare/wind10m_N_web_';
$myImageExt=".png";
# Images with local copy (cropped and optimised)
$myUrlStub='images_2optimised/wind10m_N_web_';
$myImageExt=".optimised.png";

$myModelValidDate = $myModelInitDate;
$myDateIntervalString = "PT" . $myLoopInit . "H";;
$myModelValidDate = $myModelValidDate->add(new DateInterval($myDateIntervalString));
for ($i = $myLoopInit; $i <= 37; $i+=HOUR_INCREMENT) {
	$myImageNumber = $i+1;

	echo "<h2>" . $myDateFormatter->format($myModelValidDate) ." <span class=\"tzSmall\">". $myDateFormatterTz->format($myModelValidDate). "</span></h2>\n";
	echo "<p>";
	echo "<img src=\"" . $myUrlStub . $myImageNumber . $myImageExt . "\" ";
	echo "alt=\"Prévisions météo Bonifacio Archipel Maddalena " . $myDateFormatter->format($myModelValidDate) . "\"/>";
	echo "</p>\n \n";
	
	$myDateIntervalString = "PT" . HOUR_INCREMENT . "H";;
	$myModelValidDate = $myModelValidDate->add(new DateInterval($myDateIntervalString));
}
?>
<h2 id="metaInfo">Informations</h2>
<h3>Pourquoi cette page ?</h3>
<p>J'ai réalisé cette page pour mes besoins propres de navigation dans les bouches de Bonficio et l'Archipel de la Maddalena, typiquement lors d'encadrement de stages à l'école de voile Les Glénans.</p>
<p>Le site <a href="http://www.lamma.rete.toscana.it/mare/modelli/vento-e-mare" lang="it">Consorzio LaMMA Rete</a> est particulièrement utile car il offre une représentation visuelle du champs de vent et ceci heure par heure. Par contre l'interface utilisateur du site est assez peu pratique, surtout sur téléphone mobile et en réseau 3G.</p>
<p>C'est pour répondre à ce besoin que j'ai créé cette page. Si je résume mon cahier des charges, ça donne ceci : </p>
<ul>
<li>Avoir en une seule page, les prévisions de vent toutes les 2 heures pour les 36 heures à venir.</li>
<li>Avoir l'heure légale de chaque prévision (sans devoir faire de calcul de UTC/heure légale, et ce été comme hiver).</li>
<li>Optimiser la page pour la consultation en 3G (application des techniques "webperf", i.e. minification du code, optimisation des images, réduction du nombre de requêtes DNS)</li>
</ul>
<h3>Mises à jour</h3>
<ul>
<li>7h30 <abbr title="Temps Universel Coordonné">UTC</abbr> (9h30 en été, 8h30 en hiver) s'appuyant sur les données de 0h UTC</li>
<li>21h30 UTC (23h30 en été, 22h30 en hiver) s'appuyant sur les données de 12h UTC</li>
</ul>
<p>Détail de <a href="http://www.lamma.rete.toscana.it/mare/modelli/ww3-info-sul-modello">mises à jour du modèle WW3 LaMMA RETE (en italien)</a></p>
<h3>Contact</h3>
<p>matthieu CHEZ stramanari.eu</p>
<footer>
Matthieu 2T - matthieu CHEZ stramanari.eu - août 2014
</footer>
</body>
</html>
