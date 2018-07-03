<?php 
include('../header.php');
	if(isset($_GET['subject']) && isset($_POST['category']) && !empty($_POST['category'])){
		$category_id = $_POST['category'];
		if(in_array(-1, $category_id) && isset($_POST['newcategory']) && $_POST['newcategory']){
		  foreach(explode(',', $_POST['newcategory']) as $newcategory){
			$query = 'INSERT INTO category(name) VALUES("' . $newcategory . '");';
			if($result = pg_query($link,$query)){
				$category_id[] = $link->insert_id;
			}
		 }
		}
	  $resetquery = 'DELETE FROM subjectcategories WHERE subject_id=' . $_GET['subject']. ';';
	  if($resultreset = $link->query($resetquery)){
		  
	  }
	  else{
		  echo $resetquery;
	  }
	  foreach($_POST['category'] as $category_id){
		echo $category_id;
	    $query = 'INSERT INTO subjectcategories(subject_id, category_id, verified) VALUES(' . $_GET['subject'] . ',' . $category_id . ', 1);';
	    if(pg_query($link,$query)){
	    }
	    else{
		  echo $query;
	    }
	  }
	}
	  ?>
    <main role="main" class="container">

      <div class="starter-template">
        <?php
			$query = '';
			$subject = '';
			if(isset($_GET['subject'])){
				$query = 'SELECT * FROM subject WHERE id=' . $_GET['subject']. ';';
				if($result = pg_query($link,$query)){
					$subject =pg_fetch_assoc($result)['name'];
				}
			}
		  ?>
		  <h1><?php echo '<a href="/text.php?subject=' . $_GET['subject'] . '">' . $subject . '</a>'; ?></h1>
        <p class="lead"></p>		
      </div>
	  
	  <div class='row'>
		<div class='col-lg-9'>
		  <p>
		  <?php
			$votequery = 'SELECT * FROM vote WHERE subject_id=' . $_GET['subject'] . ';';
			if($resultvote = $link->query($votequery)){
				if($resultvote->num_rows > 0){
					while($vote = $resultvote->fetch_assoc()){
						echo '<h3>' . $vote['name'] . '</h3>';
						$query = 'SELECT voteperparty.pro AS pro, voteperparty.against AS against, party.name AS party FROM voteperparty, party WHERE voteperparty.party_id = party.id AND vote_id=' . $vote['id'] . ';';
						if($resultperparty = pg_query($link,$query)){
							echo '<table class="table table-striped table-sm"><thead><tr><th>Partij</th><th>Voor</th><th>Tegen</th></tr></thead><tbody>';
							while($partyresult = $resultperparty->fetch_assoc()){
								echo '<tr><td>' . $partyresult['party'] . '</td><td>' . $partyresult['pro'] . '</td><td>' . $partyresult['against'] . '</td></tr>';
							}
							echo '</tbody></table>';
						}
						else{
							echo $query;
						}
					}
				}
				else{
					$query = '';
					if(isset($_GET['person']) && isset($_GET['subject'])){
						$query = 'SELECT person.id AS id, person.name AS person, statement.text AS text FROM statement, person WHERE subject_id=' . $_GET['subject'] . ' AND person_id=' . $_GET['person'] . ' AND person.id = statement.person_id;';
					}
					else if(isset($_GET['subject'])){
						$query = 'SELECT person.id AS id, person.name AS person, statement.text AS text FROM statement, person WHERE subject_id=' . $_GET['subject'] . ' AND person.id = statement.person_id;';
					}
					if($query != ''){
						if($result = pg_query($link,$query)){
							while($text =pg_fetch_assoc($result)){
								if($text['person'] != ''){
									echo '<strong><a href="/person.php?person=' . $text['id'] . '">' . $text['person'] . '</a></strong>: ';
								}
								echo $text['text'] . '<br>';
							}
						}
					}
				}
			}
			else{
				echo $votequery;
				
			}
		  ?>
		  </p>
		</div>
		<div class='col-lg-3'>
		  <div class='headline'>
		    <?php
			  $minute = '';
			  $subject = '';
			  $dossier = '';
			  $dossier_id = 0;
			  $category_id = [];
			  if(isset($_GET['subject'])){
				$query = 'SELECT subject.name AS subject, subject.dossier_id AS dossier, minute.name AS minute, dossier.onlineid AS onlineid, dossier.id AS id FROM subject, minute, dossier  WHERE 	dossier.id=subject.dossier_id AND subject.id=' . $_GET['subject'] . ' AND minute.id = subject.minute_id;';
				if($result = pg_query($link,$query)){
					$row =pg_fetch_assoc($result);
					$subject = $row['subject'];
					$minute = $row['minute'];
					$dossier = ($row['onlineid'] > 0 ? $row['onlineid']  : '');
					$dossier_id = $row['id'];
				}
				else{
					echo $query;
				}
				
				$query = 'SELECT * FROM subjectcategories  WHERE subject_id=' . $_GET['subject'] . ';';
				if($result = pg_query($link,$query)){
					while($row=$result->fetch_assoc()){
						$category_id[] = $row['category_id'];
					}
				}
				else{
					echo $query;
				}
		      }
			  $query2 = 'SELECT keyword FROM keyword WHERE subject_id="' . $_GET['subject'] . '" LIMIT 40;';
			  if($result2 = pg_query($link,$query2)){
				$keywordstring = '';
				while( $keyword = pg_fetch_assoc($result2)){
					$keywordstring .= '<a href=/?search=' . $keyword['keyword'] . '>' . $keyword['keyword'] . '</a>, '; 
				}
			  }?>
			<p><?php echo '<strong>Plenair Verslag: </strong>' . $minute ?></p>
			<p><?php echo '<strong>Datum: </strong>' . substr($minute , 21) ?></p>
			<p><?php echo '<strong>Dossier: </strong><a href="/dossier.php?dossier=' . $dossier_id  . '">' . $dossier . '</a>' ?></p>
			<p><?php echo '<strong>Keywords: </strong><br />' . $keywordstring ?></p>
			<p>Category<br></p>
			<form method='post' action=''>
				<?php if($result3 = $link->query('SELECT * FROM category;')){
					while($category = $result3->fetch_assoc()){?>
						<input type='checkbox' name='category[]' <?php echo ( in_array($category['id'], $category_id) ? 'checked' : '');?> value='<?php echo $category['id'];?>'><?php echo $category['name']; ?><br>
				<?php
					}
				}
				?>
				<input type='checkbox' name='category[]' value='-1'><input type='textfield' placeholder='Other' name='newcategory'><br>
				<input type='submit'>
			</form>
				
			</form>
		  </div>
		</div>
	  </div>
    </main><!-- /.container -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>
  </body>
</html>