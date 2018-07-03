<?php
session_start();
include('./loginscript.php');
include('../header.php');
?>
<main role="main" class="container">
    <?php
	$query = '';
	$subject = '';
	if(isset($_GET['category'])){
		$query = 'SELECT * FROM category WHERE id=' . $_GET['category']. ';';
		if($result = pg_query($link,$query)){
			$category =pg_fetch_assoc($result);
			?>
			<div class="starter-template">
				<h1><<?php echo $category['name'];?></h1>
			</div>
			<div class='row results'>
				<?php
				$query2 = 'SELECT DISTINCT dossier.id AS id, dossier.name AS name, minute.date FROM subject, category, subjectcategories, dossier, subject_to_dossier minute WHERE subjectcategories.category_id=' . $_GET['category'] . ' AND 
				subject.id=subjectcategories.subject_id AND category.id=subjectcategories.category_id AND subject.minute_id=minute.id AND subject.id=subject_to_dossier.subject_id AND dossier.id=subject_to_dossier.dossier_id ORDER BY minute.date DESC;';
				if($result2 = pg_query($link,$query2)){
					while($dossier = pg_fetch_assoc($result2)){
				?>
						<div class='col-lg-4'>
							<a href='<?php echo '/dossier.php?dossier=' . $dossier['id'];?>'><h4><?php echo $dossier['name'];?></h4></a>
						</div>
				<?php
					}
				}
				?>
			</div>
	<?php
		}
	}
	else
	{
		?>
		<div class='row results'>
			<?php
			$query2 = 'SELECT * FROM category ORDER BY name ASC;';
			if($result2 = pg_query($link,$query2)){
				while($category = pg_fetch_assoc($result2)){
			?>
					<div class='col-lg-4'>
						<a href='<?php echo '/category.php?category=' . $category['id'];?>'><h4><?php echo $category['name'];?></h4></a>
					</div>
			<?php
				}
			}
			?>
		</div>
		<?php
	}
	?>
</main>
<?php
include('footer.php');
?>