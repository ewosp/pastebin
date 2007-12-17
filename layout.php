<?php
/**
 * $Project: Pastebin $
 * $Id: layout.php,v 1.1 2006/04/27 16:22:39 paul Exp $
 * 
 * Pastebin Collaboration Tool
 * http://pastebin.com/
 *
 * This file copyright (C) 2006 Paul Dixon (paul@elphin.com)
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */
 
echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?php echo $page['title'] ?></title>
<meta name="ROBOTS" content="NOARCHIVE" />
<link rel="stylesheet" type="text/css" media="screen" href="/pastebin.css?ver=4" />
<link rel="shortcut icon" href="favicon.ico" />
<?php if (isset($page['post']['codecss']))
{
	echo '<style type="text/css">';
	echo $page['post']['codecss'];
	echo '</style>';
}
?>
<script type="text/javascript" src="/pastebin.js?ver=3"></script>
</head>


<body onload="initPastebin()">
<div style="display:none;">
<h1 style="display: none;">Pastebin :: outil de debug collaboratif et de copier/coller de long textes</h1>
<p style="display: none;">Une pastebin est un utilitaire vous permettant de publier un code snippet (un extrait de code source), un rapport d'erreur, un long texte, ...lorsque vous chattez sur IRC, Skype, MSN, un forum, ...</p>
<p style="display: none;">Ce site a éété développé en XHTML et CSS2. Apparemment, il semblerait que votre browser ne supporte pas ces technologies.
Visitez <a href="http://www.webstandards.org/upgrade/" target="_blank">WaSP</a> pour mettre à jour !</p>
</div>

<div id="titlebar"><?php 
	echo $page['title'];
	if ($subdomain=='')
	{
		echo " <a href=\"{$CONF['this_script']}?help=1\">Consulter l'aide</a>";
	}
	else
	{
		echo " <a href=\"{$CONF['this_script']}?help=1\">Qu'est-ce qu'une pastebin privée ?</a>";
	}
	
?>
</div>



<div id="menu">

<h1>Posts récents</h1>
<ul>
<?php  
	foreach($page['recent'] as $idx=>$entry)
	{
		if ($entry['pid']==$pid)
			$cls=" class=\"highlight\"";
		else
			$cls="";
			
		echo "<li{$cls}><a href=\"{$entry['url']}\">";
		echo $entry['poster'];
		echo "</a><br/>{$entry['agefmt']}</li>\n";
	}
?>
<li><a href="<?php echo $CONF['this_script'] ?>">Nouveau post</a></li>
</ul>

<!--
<h1>Besoin d'aide ?</h1>
<p>Notre canal IRC #Win et notre forum sont à votre disposition pour vous aider dans vos problèmes de prog, de serveurs (Windows, BSD, Linux), de réseaux, ... :</p>
<ul>
	<li><a href='http://chat.espace-win.org'>Rejoindre le chat</a></li>
	<li><a href='http://www.espace-win.iorg/IRC/'>Le site de #Win</a></li>
	<li><a href='http://forum.espace-win.org/'>Le forum</a></li>
</ul>
-->

<?php
if ($subdomain=='')
{
?>

<h1>Sous-domaine gratuit</h1>
<p>Vous voulez un sous-domaine pour votre communauté ?
Il suffit d'indiquer l'url dans la barre d'adresse et hop, c'est créé.
<a href="<?php echo $CONF['this_script'].'?help=1' ?>">Consultez l'aide</a> pour plus d'informations.</p>
	
<?php 
}
?>

<h1>À propos</h1>
<p>Pastebin est un outil de debug collaboratif et de copier/coller de long textes, <a href="<?php echo $CONF['this_script'].'?help=1' ?>">consultez l'aide</a>
pour plus d'informations.</p>

<form method="get" action="http://fr.php.net/search.php">
<h1>Manuel PHP</h1>
<input type="text" size="9" name="pattern"/>
<input type="hidden" name="show" value="quickref"/>
<input type="submit" value="go"/>
</form>

<form method="get" action="http://www.mysql.com/search/?">
<h1>Manuel MySQL</h1>
<input type="hidden" name="base" value="http://dev.mysql.com"/>
<input type="hidden" name="lang" value="en"/>
<input type="hidden" name="doc" value="1"/>
<input type="hidden" name="m" value="o"/>
<input type="text" size="9" name="q"/>
<input type="submit" value="go"/>
</form>

</div>


<div id="content">
	
	<?php
/*
 * Google AdWords block is below - if you re-use this script, be sure
 * to configure your own AdWords client id!
 */
if (strlen($CONF['google_ad_client'])) 
{
?>
<script type="text/javascript"><!--
google_ad_client = "<?php echo $CONF['google_ad_client'] ?>";
google_ad_width = 728;
google_ad_height = 90;
google_ad_format = "728x90_as";
google_ad_type = "text_image";
google_ad_channel ="";
google_color_border = "FFFFFF";
google_color_bg = "FFFFFF";
google_color_link = "0099CC";
google_color_url = "888888";
google_color_text = "000000";
//--></script>
<script type="text/javascript"
  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
<br/>
<br/>
<?php
}

///////////////////////////////////////////////////////////////////////////////
// show processing errors
//
if (count($pastebin->errors))
{
	echo "<h1>Erreurs</h1><ul>";
	foreach($pastebin->errors as $err)
	{
		echo "<li>$err</li>";
	}
	echo "</ul>";
	echo "<hr />";
}


if (isset($_REQUEST["diff"]))
{
	
	$newpid=intval($_REQUEST['diff']);
	
	$newpost=$pastebin->getPost($newpid);
	if (count($newpost))
	{
		$oldpost=$pastebin->getPost($newpost['parent_pid']);	
		if (count($oldpost))
		{
			$page['pid']=$newpid;
			$page['current_format']=$newpost['format'];
			$page['editcode']=$newpost['code'];
			$page['posttitle']='';
	
			//echo "<div style=\"text-align:center;border:1px red solid;padding:5px;margin-bottom:5px;\">Diff feature is in BETA! If you have feedback, send it to lordelph at gmail.com</div>";
			echo "<h1>Différences entre :<br/>- le nouveau post, n° <a href=\"".$pastebin->getPostUrl($newpost['pid'])."\">{$newpost['pid']}</a> par {$newpost['poster']}, le {$newpost['postdate']} et<br/>".
				"- le post original, n° <a href=\"".$pastebin->getPostUrl($oldpost['pid'])."\">{$oldpost['pid']}</a> par {$oldpost['poster']}, le {$oldpost['postdate']}<br/>";
			
			echo "Afficher ";
			echo "<a title=\"Ne pas afficher les lignes ajoutées ou modifiées\" style=\"padding:1px 4px 3px 4px;\" id=\"oldlink\" href=\"javascript:showold()\">la version originale</a> | ";
			echo "<a title=\"Ne pas afficher les lignes supprimées de la version originale\" style=\"padding:1px 4px 3px 4px;\" id=\"newlink\" href=\"javascript:shownew()\">la nouvelle version</a> | ";
			echo "<a title=\"Afficher les insertions commes les suppressions\"  style=\"background:#880000;padding:1px 4px 3px 4px;\" id=\"bothlink\" href=\"javascript:showboth()\">les deux versions</a> ";
			echo "</h1>";
			
			$newpost['code']=preg_replace('/^'.$CONF['highlight_prefix'].'/m', '', $newpost['code']);
			$oldpost['code']=preg_replace('/^'.$CONF['highlight_prefix'].'/m', '', $oldpost['code']);
			
			$a1=explode("\n", $newpost['code']);
			$a2=explode("\n", $oldpost['code']);
			
			$diff=new Diff($a2,$a1, 1);
			
			echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"diff\">";
			echo "<tr><td></td><td></td><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td></td></tr>";
			echo $diff->output;
			echo "</table>";
		}
		
	}
	
	
}

///////////////////////////////////////////////////////////////////////////////
// show a post
//

if (isset($_GET['help']))
	$page['posttitle']="";
	
if (strlen($page['post']['posttitle']))
{
		echo "<h1>{$page['post']['posttitle']}";
		if ($page['post']['parent_pid']>0)
		{
			echo " (modification of post by <a href=\"{$page['post']['parent_url']}\" title=\"view original post\">{$page['post']['parent_poster']}</a> ";
			echo "<a href=\"{$page['post']['parent_diffurl']}\" title=\"compare differences\">Voir le diff</a>)";
		}
		
		echo "<br/>";
		
		$followups=count($page['post']['followups']);
		if ($followups)
		{
			echo "View followups from ";
			$sep="";
			foreach($page['post']['followups'] as $idx=>$followup)
			{
				echo $sep."<a title=\"posted {$followup['postfmt']}\" href=\"{$followup['followup_url']}\">{$followup['poster']}</a>";
				$sep=($idx<($followups-2))?", ":" and ";	
			}
			
			echo " | ";
		}
		
		if ($page['post']['parent_pid']>0)
		{
			echo "<a href=\"{$page['post']['parent_diffurl']}\" title=\"Comparer les différences\">Diff</a> | ";
		} 
		
		echo "<a href=\"{$page['post']['downloadurl']}\" title=\"Télécharger le fichier\">Télécharger</a> | ";
		
		echo "<span id=\"copytoclipboard\"></span>";
		
		echo "<a href=\"/\" title=\"Créer un tout nouveau post\">Nouveau post</a>";
		
		echo "</h1>";
}
if (isset($page['post']['pid']))
{
	echo "<div class=\"syntax\">".$page['post']['codefmt']."</div>";
	echo "<br /><b>Proposer une correction ou une modification du code ci-dessous
	(<a href=\"{$CONF['this_script']}\">cliquez ici pour un nouveau post</a>)</b><br/>";
	echo "Après avoir proposé votre modification, vous pourrez facilement voir les différences entre l'ancien et le nouveau post.";
}	

if (isset($_GET['help']))
{
	?>
	<h1>Qu'est-ce qu'une pastebin ?</h1>
	<p>Une pastebin est un utilitaire vous permettant de publier un code snippet (un extrait de code source), un rapport d'erreur, un long texte, ...</p>
	<p>Si vous n'êtes pas familier avec ce concept, voyons comment les autres l'utilisent :</p>
	<ul>
	<li><a href="/">publier</a> un fragment de code source afin d'obtenir une url comme http://<?= TOPDOMAIN ?>/1234</li>
	<li>coller l'URL sur <a href='http://www.espace-win.org/IRC/'>IRC</a>, messagerie instantanée, ...</li>
	<li>quelqu'un vous répond en lisant et peut-être en proposant une modification de votre code</li>
	<li>vous pouvez alors voir les modifications, notre outil de diff peut même vous y aider</li>
	</ul>
	
	<h1>Comment puis-je voir les différences entre deux posts ?</h1>
	<p>Lorsque vous regardez un post, vous pouvez l'éditer. Cela <strong>crée un nouveau post</strong> avec cette particularité :
	il contient un <strong>lien 'diff'</strong> qui vous permet de comparer l'ancienne et la nouvelle version.</p>
	<p>C'est une fonctionnalité des plus puissantes pour repérer quelles lignes ont exactement été modifiées.</p>
	
	<h1>Qu'est-ce qu'une pastebin privée et comment puis-je l'utiliser ?</h1>
	<p>Vous disposez d'une pastebin privée simplement en pensant à un nom de domaine que personne
	d'autre n'utilise, par exemple http://wazza.<?= TOPDOMAIN ?> ou http://projet-vifazur.<?= TOPDOMAIN ?>.
	Tous les posts qui y seront effectués ne seront visibles que sur ce domaine, facilitant ainsi l'entraide,
	la collaboration au sein d'un groupe d'utilisateurs sans le 'bruit' du service régulier de
	<a href="http://<?= TOPDOMAIN ?>">http://<?= TOPDOMAIN ?></a>.</p>

	<p>Tout ce que vous avez à faire est de changer l'url dans votre browser pour créer ou accéder à une pastebin privé, ou simplement entrer ci-dessous le domaine que vous souhaitez :</p>
	
	<form method="get" action="<?php echo $CONF['this_script']?>">
	<input type="hidden" name="help" value="1"/>
	<p>Me rendre sur http://<input type="text" name="goprivate" value="<?php echo stripslashes($_GET['goprivate']) ?>" size="10"/>.<?= TOPDOMAIN ?>
	<input type="submit" name="go" value="Go"/></p>
	<?php if (isset($_GET['goprivate'])) { echo "<p>Merci de n'utiliser que des caractères alphanumériques (a-z, 0-9), des tirets ('-') ou des points ('.'). Le premier caractère doit obligatoirement être un lettre ou un chiffre.</p>"; } ?>
	</form>
	
	<p>Attention, il n'y a pas de protection par mot de passe, les sous-domaines sont accessibles par quiconque connaît l'URL (par contre nous ne publions pas la liste des domaines utilisés).</p>
	
	<h1>Sous-domaines pour votre langage ...</h1>
	
	<p>Si un sous-domaine correspond au nom d'un language, la coloration syntaxique de ce langage sera appliquée par défaut.</p>
	<p>Ainsi, si vous vous rendez sur tcl.<?= TOPDOMAIN ?>, vous verrez que TCL est sélectionné par défaut.</p>
	
	<p><?php 
	
	$sep="";
	foreach($CONF['all_syntax'] as $langcode=>$langname)
	{
		if ($langcode=='text')
			$langname="Texte brut";
		echo "{$sep}<a title=\"{$langname} Pastebin\" href=\"http://{$langcode}.", TOPDOMAIN, "\">{$langname}</a>";
		$sep=", ";
	}	
		
		
		?></p>
	
	<h1>Et c'est entièrement gratuit ?</h1>
	<p>En effet, et cela le restera. Ce service vous est offert par <a href='http://www.espace-win.org/'>Espace Win</a>.</p>
	
	<h1>Puis-je obtenir le code source ?</h1>
	<p>pastebin est un logiciel PHP open source, diffusé sous licence GPL. Le code source (en anglais) est <a href='http://www.pastebin.com/pastebin.tar.gz'>librement téléchargable ici</a>.</p>
	
	<h1>Où puis-je adresser mes commentaires ?</h1>
	<p>Soit sur le canal #Win, soit en utilisant la fenêtre de feedback à gauche.</p>
	<p>Pour joindre le développeur de pastebin, envoyez un e-mail à <script type="text/javascript">eval(unescape('%64%6f%63%75%6d%65%6e%74%2e%77%72%69%74%65%28%27%3c%61%20%68%72%65%66%3d%22%6d%61%69%6c%74%6f%3a%70%61%75%6c%40%65%6c%70%68%69%6e%2e%63%6f%6d%22%20%3e%50%61%75%6c%20%44%69%78%6f%6e%3c%2f%61%3e%27%29%3b'))</script>.</p>
	<p>Pour signaler une erreur dans la traduction française, contactez Sébastien Santoro (Dereckson) via le fenêtre de feedback.</p>
	
	<?php
}
else
{
?>
<form name="editor" method="post" action="<?php echo $CONF['this_script']?>">
<input type="hidden" name="parent_pid" value="<?php echo $page['post']['pid'] ?>"/>

<br/>Coloration syntaxique : <select name="format">
<?php

//show the popular ones
foreach ($CONF['all_syntax'] as $code=>$name)
{
	if (in_array($code, $CONF['popular_syntax']))
	{
		$sel=($code==$page['current_format'])?"selected=\"selected\"":"";
		echo "<option $sel value=\"$code\">$name</option>";
	}
}

echo "<option value=\"text\">----------------------------</option>";

//show all formats
foreach ($CONF['all_syntax'] as $code=>$name)
{
	$sel=($code==$page['current_format'])?"selected=\"selected\"":"";
	if (in_array($code, $CONF['popular_syntax']))
		$sel="";
	echo "<option $sel value=\"$code\">$name</option>";
	
}
?>
</select><br/>
<br/>

Pour lutter contre le spam, hop un petit calcul simple et une devinette encore plus simple pour prouver que vous êtes un humain :<br />
Quelle est la couleur du cheval blanc d'henri IV ? <input type="text" name="quux2" size=8 />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<img align="top" src="verify.php" /><input type="text" name="quux" size=3 />
<br /><br />

Pour mettre en évidence certaines lignes, précedez chacune d'entre elles par <?php echo $CONF['highlight_prefix'] ?>.<br/>
<textarea id="code" class="codeedit" name="code2" cols="80" rows="10" onkeydown="return catchTab(this,event)"><?php 
echo htmlentities($page['post']['editcode']) ?></textarea>

<div id="namebox">
	
<label for="poster">Votre nom</label><br/>
<input type="text" maxlength="24" size="24" id="poster" name="poster" value="<?php echo $page['poster'] ?>" />
<input type="submit" name="paste" value="Send"/>
<br />
<input type="checkbox" name="remember" value="1" <?php echo $page['remember'] ?>/>Se souvenir de mes réglages

</div>


<div id="expirybox">


<div id="expiryradios">
<label>Combien de temps ce post doit-il être conservé ?</label><br/>

<input type="radio" id="expiry_day" name="expiry" value="d" <?php if ($page['expiry']=='d') echo 'checked="checked"'; ?> />
<label id="expiry_day_label" for="expiry_day">un jour</label>

<input type="radio" id="expiry_month" name="expiry" value="m" <?php if ($page['expiry']=='m') echo 'checked="checked"'; ?> />
<label id="expiry_month_label" for="expiry_month">un mois</label>

<input type="radio" id="expiry_forever" name="expiry" value="f" <?php if ($page['expiry']=='f') echo 'checked="checked"'; ?> />
<label id="expiry_forever_label" for="expiry_forever">définitivement</label>
</div>

<div id="expiryinfo"></div>
</div>

<div id="end"></div>

</form>
<?php 
} 
?>

</div>
</body>
</html>
