<? 

/*	
	This is just a simple template that includes "kino-tus-maribor.php" and displays content.
	Demo is visible @ http://dynfetcher.herokuapp.com/
*/

require "kino-tus-maribor.php";

?>
<!DOCTYPE html>
<html>
	<meta charset='utf-8'>
	<title>DynFetcher Demo</title>
	<style>
		body,td,th,p{ font-family: 'Open Sans',helvetica, sans-serif; }
		body,html{ margin:0px; padding:0px; }
		body{ padding:10px; }
		a{ color:red; text-decoration: none }
		ul,li{ list-style: none; margin: 0px; padding: 0px; }
		.wrap{ margin:0 auto; }
		.film{ margin-right:0px; margin-bottom:0px; width: 200px; height: 300px; display: inline-block; position: relative; }
		.film .details{ display: none; position: absolute; left:0px; top:0px; width:160px; height:300px; padding:20px;}
		.film:hover{ background-color:white; }
		.film:hover .details{ display: block; background-color:rgba(255, 255, 255, 0.8); }
		ul.zvrsti { display: block; height:30px; line-height: 30px; width: 100%;}
		.zvrsti li{ float:left;  height:30px; line-height: 30px; margin-right:15px; }
		</style>

	</style>
	<body>
		<a href="https://github.com/matjaz/dynfetcher"><img style="position: absolute; top: 0; right: 0; border: 0;" src="https://s3.amazonaws.com/github/ribbons/forkme_right_green_007200.png" alt="Fork me on GitHub"></a>

		<div class="wrap">
		<div class="header">
			<h1><?= date("Y-m-d",strtotime("now")) ?> @ <a href="http://www.planet-tus.si/">Planet Tuš Maribor</a> via <a href="https://github.com/matjaz/dynfetcher">DynFetcher</a> </h1>
			
			<?
			$zvrsti = array();
			foreach($filmi as $film)
				foreach($film['zvrst'] as $zvrst)
					if(!in_array($zvrst, $zvrsti)) $zvrsti[]=$zvrst;
			?>

			<ul class="zvrsti">
				<li><a href="#all" data-filter="*">All</a></li>
				<? foreach ($zvrsti as $zvrst): ?>
				<li><a href="#<?= $zvrst ?>" data-filter=".<?= $zvrst ?>"><?= ucfirst($zvrst); ?></a></li>
			<? endforeach; ?></ul>

		</div>

		<div class="filmi-wrap">
		<div class="filmi"><? foreach($filmi as $film): ?>
			<div class="film <?= implode(' ',$film['zvrst']); ?>">
				<img src="<?= $film['cover'] ?>"/>
				<div class="details">
					<h2><?= $film["naslov"] ?></h2>
					<? foreach($film["ure"] as $ura): ?>
						<strong><?= $ura["ura"]; ?></strong>
						(<?= $ura["cena"] ?> &euro;)
					<? endforeach; ?>
				</div>
			</div>
		<? endforeach; ?></div></div>

	</div>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>	
	<script src="http://isotope.metafizzy.co/jquery.isotope.min.js"></script>

	<script type="text/javascript">
	$(function(){
		var iso = $('div.filmi');
		iso.isotope({ itemSelector: '.film', layoutMode: 'masonry' });
		$(".zvrsti a").click(function(e){
			if(e.preventDefault) e.preventDefault();
			iso.isotope({filter: $(this).data("filter")});
		});
	});
	</script>
	</body>
</html>