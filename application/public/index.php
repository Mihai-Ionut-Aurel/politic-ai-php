<?php
    session_start();
    if(isset($_GET['logout'])){
        session_unset();
    }
	include('../header.php');
?>
    <div class="position-relative overflow-hidden p-3 p-md-5 text-center bg-light hero">
      <div class="col-md-5 p-lg-5 mx-auto my-2">
        <h1 class="display-4 font-weight-normal">PoliticAI</h1>
        <p class="lead font-weight-normal">De tool die de politiek transparant maakt.</p>
      </div>
	  <!-- Begin MailChimp Signup Form -->
      <link href="//cdn-images.mailchimp.com/embedcode/horizontal-slim-10_7.css" rel="stylesheet" type="text/css">
	  <style type="text/css">
			#mc_embed_signup{clear:left; font:14px Helvetica,Arial,sans-serif; width:100%;}
      </style>
      <div id="mc_embed_signup">
		<form action="https://politicai.us18.list-manage.com/subscribe/post?u=e162e3b4a1304a161249feafa&amp;id=aeee2e2bfa" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
		  <div id="mc_embed_signup_scroll">
		    <label for="mce-EMAIL">Schrijf je in voor onze nieuwsbrief.</label>
		    <input type="email" value="" name="EMAIL" class="email" id="mce-EMAIL" placeholder="email address" required>
			<!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
			<div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_e162e3b4a1304a161249feafa_aeee2e2bfa" tabindex="-1" value=""></div>
			<div class="clear"><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
		  </div>
		</form>
	  </div>
<!--End mc_embed_signup-->
    </div>

    <!--div class="d-md-flex flex-md-equal w-100 my-md-6 pl-md-6">
      <div class="bg-dark mr-md-3 pt-3 px-3 pt-md-5 px-md-5 text-center text-white overflow-hidden">
        <div class="my-3 py-3">
          <h2 class="display-5">Find politicians' opinions</h2>
          <p class="lead">Look for opinions per politician or per subject.</p>
        </div>
        <div class="bg-light box-shadow mx-auto" style="width: 80%; height: 300px; border-radius: 21px 21px 0 0;">
			<div class='featureinnerdiv'>
				<img class='featurefield' src='/img/opinion.png'>
			</div>
		</div>
      </div>
      <div class="bg-light mr-md-3 pt-3 px-3 pt-md-5 px-md-5 text-center overflow-hidden">
        <div class="my-3 p-3">
          <h2 class="display-5">Find how they vote their opinion.</h2>
          <p class="lead">Do politicians actually vote what they say.</p>
        </div>
        <div class="bg-dark box-shadow mx-auto" style="width: 80%; height: 300px; border-radius: 21px 21px 0 0;">
			<div class='featureinnerdiv'>
				<img class='featurefield' src='/img/vote.png'>
			</div>
		</div>
      </div>
    </div-->
    <div class="showcase">
        <div class="container">
            <div class="row py-3 my-3">
                <div class="col-md-12">
                    <div class="offset-md-3 col-md-6 my-3">
                        <img class='img-fluid' src='/img/preview.png'>
                    </div>
                    <h2>Inzicht in wat er gezegd wordt in de kamer over ieder onderwerp.</h2>
                </div>
            </div>
        </div>
    </div>
    <div class="usp">
        <div class="container">
            <div class="row py-3">
                <div class="col-md-6">
                    <div class="card h-md-150 flex-md-row">
                        <i class="float-left flex-auto d-none d-lg-block fas fa-archive fa-3x"></i>
                        <div class="card-body d-flex flex-row align-items-center">
                            <h5>Alle informatie uit de tweede kamer op een plek.</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 ">
                    <div class="card h-md-150 flex-md-row">
                        <i class="float-left flex-auto d-none d-lg-block fas fa-file-alt fa-3x"></i>
                        <div class="card-body d-flex flex-row align-items-center">
                            <h5>Creeër je eigen overzichtspagina.</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-md-150 flex-md-row">
                        <i class="float-left flex-auto d-none d-lg-block fas fa-envelope fa-3x"></i>
                        <div class="card-body d-flex flex-row align-items-center">
                            <h5>E-mail notificaties wanneer er updates zijn over jouw onderwerpen.</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-md-150 flex-md-row">
                        <i class="float-left flex-auto d-none d-lg-block fab fa-twitter fa-3x"></i>
                        <div class="card-body d-flex flex-row align-items-center">
                            <h5>Inclusief relevante tweets.</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--div class="d-md-flex flex-md-equal w-100 my-md-6 pl-md-6">
      <div class="bg-light mr-md-3 pt-3 px-3 pt-md-5 px-md-5 text-center overflow-hidden">
        <div class="my-3 p-3">
          <h2 class="display-5">Reduce research time</h2>
          <p class="lead">Find all the information on any politician in one place.</p><br>
        </div>
        <div class="bg-dark box-shadow mx-auto" style="width: 80%; height: 300px; border-radius: 21px 21px 0 0;">
			<div class='featureinnerdiv'>
				<img class='featurefield' src='/img/search.png'>
			</div>
		</div>
      </div>
      <div class="bg-primary mr-md-3 pt-3 px-3 pt-md-5 px-md-5 text-center text-white overflow-hidden">
        <div class="my-3 py-3">
          <h2 class="display-5">Find more interesting information</h2>
          <p class="lead">We collect information from a wide range of sources, from parlament speeches to news articles and from tv interviews to voting procedures.</p>
        </div>
        <div class="bg-light box-shadow mx-auto" style="width: 80%; height: 300px; border-radius: 21px 21px 0 0;">
			<div class='featureinnerdiv'>
				<img class='featurefield' src='/img/dossier.png'>
			</div>
		</div>
      </div>
    </div-->


    <!--div class="d-md-flex flex-md-equal w-100 my-md-3 pl-md-3">
      <div class="bg-light mr-md-3 pt-3 px-3 pt-md-5 px-md-5 text-center overflow-hidden">
        <div class="my-3 p-3">
          <h2 class="display-5">Another headline</h2>
          <p class="lead">And an even wittier subheading.</p>
        </div>
        <div class="bg-white box-shadow mx-auto" style="width: 80%; height: 300px; border-radius: 21px 21px 0 0;"></div>
      </div>
      <div class="bg-light mr-md-3 pt-3 px-3 pt-md-5 px-md-5 text-center overflow-hidden">
        <div class="my-3 py-3">
          <h2 class="display-5">Another headline</h2>
          <p class="lead">And an even wittier subheading.</p>
        </div>
        <div class="bg-white box-shadow mx-auto" style="width: 80%; height: 300px; border-radius: 21px 21px 0 0;"></div>
      </div>
    </div>

    <div class="d-md-flex flex-md-equal w-100 my-md-3 pl-md-3">
      <div class="bg-light mr-md-3 pt-3 px-3 pt-md-5 px-md-5 text-center overflow-hidden">
        <div class="my-3 p-3">
          <h2 class="display-5">Another headline</h2>
          <p class="lead">And an even wittier subheading.</p>
        </div>
        <div class="bg-white box-shadow mx-auto" style="width: 80%; height: 300px; border-radius: 21px 21px 0 0;"></div>
      </div>
      <div class="bg-light mr-md-3 pt-3 px-3 pt-md-5 px-md-5 text-center overflow-hidden">
        <div class="my-3 py-3">
          <h2 class="display-5">Another headline</h2>
          <p class="lead">And an even wittier subheading.</p>
        </div>
        <div class="bg-white box-shadow mx-auto" style="width: 80%; height: 300px; border-radius: 21px 21px 0 0;"></div>
      </div>
    </div-->

<?php include('footer.php');?>