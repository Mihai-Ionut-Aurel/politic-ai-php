<?php
session_start();
include('./loginscript.php');
include('../header.php');

function getPersons($subject){
    global $link;
    $print = '';
    $query = 'SELECT DISTINCT person.id AS id, person.name AS name FROM person, subject, statement WHERE statement.subject_id=subject.id AND person.id=statement.person_id AND subject.id=' . $subject . ';';
    if($result = pg_query($link, $query)){
        while($row = pg_fetch_assoc($result)){
            $print .= '<a href="#" data-field="' . $row['id'] . '" data-enabled="1" class="badge badge-primary" onclick="personswitch(' . $row['id'] . ')">' . $row['name'] . '</a>';
        }
    }
    return $print;
}

function debug_to_console($data)
{
    $output = $data;
    if (is_array($output)) {
        $output = implode(',', $output);
    }

    echo "<script>console.log( 'Debug Objects: \'".$output."\'' );</script>";
}

function getTweets($subject, $threshold, $count)
{
    global $link;
    // the microservice is dissable as it requieres a small EC2 instance, which out measures the scope of the project now
    //$url_microservice = getenv("MICROSERVICE_URL");
//    $results = json_decode(
//        file_get_contents(
//            $url_microservice."/tweets/subject/?subject_id=".$subject
//            ."&threshold=".$threshold."&count=".$count
//        ), true
//    );
    $query = 'SELECT tweets.tweet_id as tweet_id FROM politicalai_ict.tweets, politicalai_ict.related_tweets
                where tweets.id = related_tweets.tweet_id and related_tweets.subject_id = ' .$subject.';';
    if ($results = pg_query($link, $query)) {
        $tweets = "<br>";
        while($row = pg_fetch_assoc($results)){
            $embeded_tweet = file_get_contents(
                "https://publish.twitter.com/oembed?url=".rawurlencode(
                    "https://twitter.com/Interior/status/".$row['tweet_id']
                )
            );
            $embeded_tweet = json_decode($embeded_tweet);
            $tweets = $tweets.$embeded_tweet->html;
            $tweets = $tweets."<br>";
        }
    } else {
        echo $query;
    }
    return $tweets;
}

if (isset($_GET['subject']) && isset($_POST['category'])
    && !empty($_POST['category'])
) {
    if (isset($_GET['subject']) && isset($_POST['category']) && !empty($_POST['category'])) {
        $category_id = $_POST['category'];
        if (in_array(-1, $category_id) && isset($_POST['newcategory']) && $_POST['newcategory']) {
            foreach (explode(',', $_POST['newcategory']) as $newcategory) {
                $query = 'INSERT INTO category(name) VALUES("' . $newcategory . '");';
                if ($result = pg_query($link, $query)) {
                    $category_id[] = $link->insert_id;
                }
            }
        }
        $resetquery = 'DELETE FROM subjectcategories WHERE subject_id=' . $_GET['subject'] . ';';
        if ($resultreset = $link->query($resetquery)) {

        } else {
            echo $resetquery;
        }
        foreach ($_POST['category'] as $category_id) {
            echo $category_id;
            $query = 'INSERT INTO subjectcategories(subject_id, category_id, verified) VALUES(' . $_GET['subject'] . ',' . $category_id . ', 1);';
            if (pg_query($link, $query)) {
            } else {
                echo $query;
            }
        }
    }
}
  ?>
<main role="main" class="container">
  <div class='row'>
  <div class='col-md-9'>
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
      <?php echo '<a href="/text.php?subject=' . $_GET['subject'] . '"><h2>' . $subject . '</h2></a>'; ?>
    </div>
    <div class="person-buttons">
      <?php echo getPersons($_GET['subject']);?>
    </div>
  </div>
  <div class='col-md-3 inpagesearch'>
    <form method='get' action='' class="form-inline my-2 my-lg-0">
      <div class="form-group mb-2">
        <input type="hidden" name="subject" value="<?php echo $_GET['subject']; ?>" />
        <input class="form-control" type="text" name='pagesearch' placeholder="Zoeken op pagina" <?php echo (isset($_GET['pagesearch']) ? 'value="' . $_GET['pagesearch'] . '"' : '' ); ?> aria-label="Search">
      </div>
      <button class="btn btn-outline-success mb-2" type="submit">Zoek</button>
    </form>
      <a href="/text.php?subject=<?php echo $_GET['subject'];?>"><button class="btn btn-primary btn-block">Reset elke selectie.</button></a>
  </div>
  </div>
  <div class='row'>
    <div class='col-lg-3'>
      <div class='headline'>
        <?php
          $minute = '';
          $subject = '';
          $dossier = '';
          $dossier_id = 0;
          $category_names = [];
          $category_id = [];
          if(isset($_GET['subject'])){
            $query = 'SELECT subject.name AS subject, subject.dossier_id AS dossier, minute.name AS minute, dossier.onlineid AS onlineid, dossier.id AS id, dossier.name AS dossiername FROM subject, minute, dossier  WHERE 	dossier.id=subject.dossier_id AND subject.id=' . $_GET['subject'] . ' AND minute.id = subject.minute_id;';
            if($result = pg_query($link,$query)){
                $row =pg_fetch_assoc($result);
                $subject = $row['subject'];
                $minute = $row['minute'];
                $dossier = ($row['onlineid'] > 0 ? $row['dossiername'] . ' (' . $row['onlineid'] . ')'  : '');
                $dossier_id = $row['id'];
            }
            else{
                echo $query;
            }
            $categorystring = [];
            $query = 'SELECT category.name AS name, category.id AS id FROM subjectcategories, category  WHERE subjectcategories.category_id=category.id AND subject_id=' . $_GET['subject'] . ';';
            if($result = pg_query($link,$query)){
                while($row = pg_fetch_assoc($result)){
                    $categorystring[] = '<a href=/category.php?category=' . $row['id'] .'>' . $row['name'] . '</a>';
                }
            }
            else{
                echo $query;
            }
          }
          $query2 = 'SELECT keyword FROM keyword WHERE subject_id=\'' . $_GET['subject'] . '\' LIMIT 40;';
          if($result2 = pg_query($link,$query2)){
            $keywordstring = '';
            while( $keyword = pg_fetch_assoc($result2)){
                $keywordstring .= '<a href=/?search=' . $keyword['keyword'] . '>' . $keyword['keyword'] . '</a>, ';
            }
          }

          ?>
        <p><?php echo '<strong>Plenair Verslag: </strong>' . $minute ?></p>
        <p><?php echo '<strong>Datum: </strong>' . substr($minute , 21) ?></p>
        <p><?php echo '<strong>Dossier: </strong><a href="/dossier.php?dossier=' . $dossier_id  . '">' . $dossier . '</a>' ?></p>
        <p><?php echo '<strong>Categoriën: </strong>' . join(', ', $categorystring) ?></p>
        <p> <?php
                        echo '<strong> Tweets:</strong>';
                        echo getTweets($_GET['subject'], 0.5, 10);
                        ?>
        </p>
      </div>
    </div>
      <div class='col-lg-9'>
          <p>
              <?php
              $votequery = 'SELECT DISTINCT vote.name AS name, vote.id AS id FROM vote LEFT JOIN voteperparty ON voteperparty.vote_id=vote.id WHERE voteperparty.id IS NOT NULL AND subject_id=' . $_GET['subject'] . ';';
              if($resultvote = pg_query($link,$votequery)){
                  if(pg_num_rows($resultvote) > 0){
                      while($vote = pg_fetch_assoc($resultvote)){
                          echo '<h3>' . $vote['name'] . '</h3>';
                          $query = 'SELECT * FROM motie WHERE vote_id=' . $vote['id'] . ';';
                          if($resultmotie = pg_query($link, $query)){
                              $motie = pg_fetch_assoc($resultmotie);
                              echo '<h4>' . $motie['title'] . '</h4>';
                              echo '<p>' . $motie['content']. '</p>';
                          }
                          $query = 'SELECT voteperparty.pro AS pro, voteperparty.against AS against, party.name AS party, party.id AS id FROM voteperparty, party WHERE voteperparty.party_id = party.id AND vote_id=' . $vote['id'] . ';';
                          if($resultperparty = pg_query($link,$query)){
                              echo '<table class="table table-striped table-sm votetable"><thead><tr><th>Partij</th><th>Voor</th><th>Tegen</th></tr></thead><tbody>';
                              while($partyresult = pg_fetch_assoc($resultperparty)){
                                  echo '<tr><td><a href="/party.php?party=' . $partyresult['id'] . '">' . $partyresult['party'] . '</td><td>' . $partyresult['pro'] . '</td><td>' . $partyresult['against'] . '</td></tr>';
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
                      if(isset($_GET['pagesearch']) && isset($_GET['subject'])){
                          $queryaddition = [];
                          foreach(explode(' ', $_GET['pagesearch']) AS $term){
                              $queryaddition[] = '(person.name ILIKE \'%' . $term . '%\' OR statement.text ILIKE \'%'. $term . '%\' OR party.name ILIKE \'' . $term . '\' )';
                          }
                          $querystring = join(' AND ', $queryaddition);
                          $query = 'SELECT person.id AS id, person.name AS person, statement.text AS text, party.id AS party_id,
                      party.name AS party_name FROM statement, person, party WHERE party.id=person.party_id AND 
                      subject_id=' . $_GET['subject'] . ' AND person.id = statement.person_id AND (' . $querystring .');';
                      }
                      else if(isset($_GET['subject'])){
                          $query = 'SELECT person.id AS id, person.name AS person, party.id AS party_id, party.name AS party_name
                , statement.text AS text FROM statement, person, party WHERE party.id=person.party_id 
                AND subject_id=' . $_GET['subject'] . ' AND person.id = statement.person_id;';
                      }
                      if($query != ''){
                          if($result = pg_query($link,$query)){
                              while($text =pg_fetch_assoc($result)){
                                  if($text['person'] != ''){
                                      echo '<div data-person="'. $text['id'] . '" class="statement">
                                <a class="personlink" href="/person.php?person=' . $text['id'] . '">' . $text['person'] . '</a>
                                (<a class="partylink" href="/party.php?party=' . $text['party_id'] . '">' . $text['party_name'] . '</a>): ';
                                  }
                                  $printingtext = $text['text'];
                                  foreach(explode(' ', $_GET['pagesearch']) AS $term){
                                      $printingtext = str_replace($term,'<strong>' . $term . '</strong>', $printingtext);
                                  }
                                  echo $printingtext . '</div>';
                              }
                          }
                          else{
                              echo pg_errormessage($link);
                          }
                      }
                      else{
                          echo 'text';
                      }
                  }
              }
              else{
                  echo $votequery;

              }
              ?>
          </p>
      </div>
  </div>
</main><!-- /.container -->
<script>
    function personswitch(id){
        var target = '[data-person="' + id + '"]';
        var fields = document.querySelectorAll(target);
        var buttonname = '[data-field="' + id + '"]';
        var button = document.querySelector(buttonname);
        if(button.getAttribute('data-enabled') == 1){
            for (var i = 0; i < fields.length; i++) {
                fields[i].setAttribute("style", "display:none;");
            }
            button.setAttribute('class', 'badge badge-secondary');
            button.setAttribute('data-enabled', '0');
        }
        else{
            for (var i = 0; i < fields.length; i++) {
                fields[i].setAttribute("style", "");
            }
            button.setAttribute('class', 'badge badge-primary');
            button.setAttribute('data-enabled', '1');
        }

    }
</script>
<?php
include('footer.php');
?>