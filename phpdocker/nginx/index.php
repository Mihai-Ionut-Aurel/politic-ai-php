<?php include('../postgres.php');;
	  include('../header.php');?>
    <main role="main" class="container">

      <div class="starter-template">
        <h1>PoliticAI</h1>
        <p class="lead"></p>
      </div>
	  <?php if(isset($_GET['search'])){
	    
	  ?>
	  <div style='' class='row search'>
		<?php	
			$query = 'SELECT subject.id AS id, subject.name AS name FROM subject, keyword WHERE keyword="' . pg_escape_string($link, strtolower( $_GET['search'])) . '" AND keyword.subject_id = subject.id;';
			if($result = pg::query($query)){
				if(count($result) > 0){
					?>
					<h2 style='width:100%; text-align:center;'>Keywords</h2>
					<div class='row results'>
					<?php
					while($row = $result){
						$href = '/text.php?subject=' . $row['id'];
						?>
						<div class='col-lg-4 col-md-6 col-xs-12'>
							<a href='<?php echo $href; ?>'><h4><?php echo $row['name']; ?></h4></a>
							<?php
								$query2 = 'SELECT keyword FROM keyword WHERE subject_id="' . pg_escape_string($link, strtolower( $row['id'])) . '" LIMIT 10;';
								if($result2 = pg::query($query2)){
									$keywordstring = '';
									while( $keyword = pg_fetch_assoc($result2)){
										$keywordstring .= '<a href=/?search=' . $keyword['keyword'] . '>' . $keyword['keyword'] . '</a>, '; 
									}
							?>	
									<p><?php echo $keywordstring  ?></p>
							<?php
								}
							?>								
						</div>	
						<?php
					}
					?>
					</div>
					<?php
				}
			}
		?>
		<?php	
			$query = 'SELECT DISTINCT person.id AS person_id, person.name AS person, subject.id AS subject_id , subject.name AS subject FROM person, subject, statement WHERE person.name LIKE "%' . pg_escape_string($link, strtolower( $_GET['search'])) . '%" AND person.id = statement.person_id AND subject.id = statement.subject_id;';
			if($result = pg::query($query)){
				if(count($result) > 0){
					?>
					<h2 style='width:100%; text-align:center;'>Personen</h2>
					<div class='row results'>
					<?php
					while($row = $result){
						$link = '/text.php?person=' . $row['person_id'] . '&subject=' . $row['subject_id'];
						?>
						<div class='col-lg-4 col-md-6 col-xs-12'>
							<a href=<?php echo $link; ?>><h4><?php echo $row['subject']; ?></h4></a>
							<p><a href='/person.php?person=<?php echo $row['person_id']?>'><?php echo $row['person']?></a></p>				
						</div>	
						<?php
					}?>
					</div>
					<?php
				}
			}
		?>
	  </div>
	  <?php } ?>
	  

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
