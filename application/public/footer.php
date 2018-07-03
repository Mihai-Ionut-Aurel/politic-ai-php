    <footer class="container py-5">
      <div class="row">
        <div class="col-12 col-md">
		  <a href='/'>
            <img src='/img/logoblack.png' class='footerlogo'>
		  </a>
        </div>
        <div class="col-6 col-md">
          <a href="/about.php"><h5>Over ons</h5></a>
          <ul class="list-unstyled text-small">
            <li><a class="text-muted" href='#' id='open-popup' onClick='showMailingPopUp()'>Mailinglijst</a></li>
            <li><strong>Tel: </strong><a class="text-muted" href="tel:+31613933545">+31 (0)6 13 93 35 45</a></li>
            <li><strong>Email: </strong><a class="text-muted" href="mailto:politicaigetintouch@gmail.com">politicaigetintouch@gmail.com</a></li>
          </ul>
        </div>
      </div>
    </footer>


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script>
      Holder.addTheme('thumb', {
        bg: '#55595c',
        fg: '#eceeef',
        text: 'Thumbnail'
      });
	  
    </script>
	<script type="text/javascript" src="//s3.amazonaws.com/downloads.mailchimp.com/js/signup-forms/popup/embed.js" data-dojo-config="usePlainJson: true, isDebug: false"></script>
	<script type="text/javascript" src="//downloads.mailchimp.com/js/signup-forms/popup/embed.js" data-dojo-config="usePlainJson: true, isDebug: false"></script>
	<script>
	function showMailingPopUp() {
		require(["mojo/signup-forms/Loader"], function(L) { L.start({"baseUrl":"mc.us18.list-manage.com","uuid":"e162e3b4a1304a161249feafa","lid":"aeee2e2bfa"}) })
		document.cookie = "MCEvilPopupClosed=; expires=Thu, 01 Jan 1970 00:00:00 UTC";
	};

	document.getElementById("open-popup").onclick = function() {showMailingPopUp()};
	</script>

    <script>
    function autocomplete(inp, arr) {
    /*the autocomplete function takes two arguments,
    the text field element and an array of possible autocompleted values:*/
    var currentFocus;
    /*execute a function when someone writes in the text field:*/
    inp.addEventListener("input", function(e) {
    var a, b, i, val = this.value;
    /*close any already open lists of autocompleted values*/
    closeAllLists();
    if (!val) { return false;}
    currentFocus = -1;
    /*create a DIV element that will contain the items (values):*/
    a = document.createElement("DIV");
    a.setAttribute("id", this.id + "autocomplete-list");
    a.setAttribute("class", "autocomplete-items");
    /*append the DIV element as a child of the autocomplete container:*/
    this.parentNode.appendChild(a);
    /*for each item in the array...*/
    for (i = 0; i < arr.length; i++) {
    /*check if the item starts with the same letters as the text field value:*/
    if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
    /*create a DIV element for each matching element:*/
    b = document.createElement("DIV");
    /*make the matching letters bold:*/
    b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
    b.innerHTML += arr[i].substr(val.length);
    /*insert a input field that will hold the current array item's value:*/
    b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
    /*execute a function when someone clicks on the item value (DIV element):*/
    b.addEventListener("click", function(e) {
    /*insert the value for the autocomplete text field:*/
    inp.value = this.getElementsByTagName("input")[0].value;
    /*close the list of autocompleted values,
    (or any other open lists of autocompleted values:*/
    closeAllLists();
    });
    a.appendChild(b);
    }
    }
    });
    /*execute a function presses a key on the keyboard:*/
    inp.addEventListener("keydown", function(e) {
    var x = document.getElementById(this.id + "autocomplete-list");
    if (x) x = x.getElementsByTagName("div");
    if (e.keyCode == 40) {
    /*If the arrow DOWN key is pressed,
    increase the currentFocus variable:*/
    currentFocus++;
    /*and and make the current item more visible:*/
    addActive(x);
    } else if (e.keyCode == 38) { //up
    /*If the arrow UP key is pressed,
    decrease the currentFocus variable:*/
    currentFocus--;
    /*and and make the current item more visible:*/
    addActive(x);
    } else if (e.keyCode == 13) {
    /*If the ENTER key is pressed, prevent the form from being submitted,*/
    e.preventDefault();
    if (currentFocus > -1) {
    /*and simulate a click on the "active" item:*/
    if (x) x[currentFocus].click();
    }
    }
    });
    function addActive(x) {
    /*a function to classify an item as "active":*/
    if (!x) return false;
    /*start by removing the "active" class on all items:*/
    removeActive(x);
    if (currentFocus >= x.length) currentFocus = 0;
    if (currentFocus < 0) currentFocus = (x.length - 1);
    /*add class "autocomplete-active":*/
    x[currentFocus].classList.add("autocomplete-active");
    }
    function removeActive(x) {
    /*a function to remove the "active" class from all autocomplete items:*/
    for (var i = 0; i < x.length; i++) {
    x[i].classList.remove("autocomplete-active");
    }
    }
    function closeAllLists(elmnt) {
    /*close all autocomplete lists in the document,
    except the one passed as an argument:*/
    var x = document.getElementsByClassName("autocomplete-items");
    for (var i = 0; i < x.length; i++) {
    if (elmnt != x[i] && elmnt != inp) {
    x[i].parentNode.removeChild(x[i]);
    }
    }
    }
    /*execute a function when someone clicks in the document:*/
    document.addEventListener("click", function (e) {
    closeAllLists(e.target);
    });
    }
    var array = ["dividend", "vvd", "rutte"];
    autocomplete(document.getElementById('search'), array);
    </script>
  </body>
</html>