<html>
	<head>
		<title>FitnessApp</title>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script>
			$.when($.ready).then(function() {

			  // Document is ready.
			  $('#login').click(function(){
			  	$(this).attr("disabled", "disabled");
			  	var email = $('#email').val();
			  	var password = $('#password').val();

			  	firebase.auth().signInWithEmailAndPassword(email, password).catch(function(error) {
						// Handle Errors here.
						var errorCode = error.code;
						var errorMessage = error.message;

						console.log(errorMessage);
						// ...
				  });

				  $('#login').attr("disabled", false);
			  })

			  $('#logout').click(function(){

			  	firebase.auth().signOut().then(function() {
					  // Sign-out successful.
					  console.log('Success: User signed out');
					}).catch(function(error) {
					  console.log('Failure: User not signed out');
					});
			  })

			  $('#update').click(function(){

			  	var user = firebase.auth().currentUser;
			  	user.updateProfile({
					  displayName: $('#displayname').val(),
					  photoURL: "https://media.licdn.com/mpr/mpr/shrink_200_200/AAEAAQAAAAAAAAQeAAAAJGIwMmI0NjVlLWYwYWUtNGQ4ZS04YTQwLTRkYmQwMmU2ZGFlMA.jpg",
					}).then(function() {
					  // Update successful.
					  console.log("user updated");
					  
					  var database = firebase.database();

					  firebase.database().ref('users/' + user.uid).set({
					    email: user.email,
					    height: $('#height').val(),
					    yob: $('#yob').val(),
					    mobile: $('#mobile').val()

					  });

					  console.log("User db data updated");

					}).catch(function(error) {
					  // An error happened.
					  console.log("Something went wrong updating the user")
					});
			  })
			});
		</script>
	</head>
	<body>
		<h1>Welcome to FitnessApp<span class="signedin" id="user"></span>!</h1><a class="signedin" href="#" id="logout">Log Out</a>
		<div class="signedin"><img id="user-image" src="" /></div>

		<div class="signedout" id="signin">
			Email: <input id="email" type="text" /><br />
			Password: <input id="password" type="password" />
			<input id="login" type="submit" value="Login" />
		</div>

		<div class="signedin" id="signedin">
			Name:<input id="displayname" type="text" /><br />
			Birth Year:<input id="yob" type="number" /><br />
			Height (cm):<input id="height" type="number" /><br />
			Mobile:<input id="mobile" type="mobile" placeholder="+441234567890" /><br />
			Lifestyle:<select><option value="lchf">Low Carb, High Fat</option><option value="lfhc">Low Fat, High Carb</option></select><br />
			Activity Level:<select id="activity">
				<option value="low">Low</option>
				<option value="light">Light</option>
				<option value="moderate">Moderate</option>
				<option value="easy">Easy</option>
				<option value="extreme">Extreme</option>

			</select><br/>
			Goal:<select id="goal">
				<option value="leangains">Build Muscle</option>
				<option value="slow">Lose weight slowly</option>
				<option value="easy">Lose weight a little quicker</option>
				<option value="medium">Challenging Weight loss</option>
				<option value="hard">Hard fat loss</option>
				<option value="difficult">Difficult fat burn</option>

			</select><br/>
			<input id="update" type="submit" value="Save!"
		</div>

	</body>

<script src="https://www.gstatic.com/firebasejs/4.9.0/firebase.js"></script>
<script>
  // Initialize Firebase
  var config = {
    apiKey: "AIzaSyAZjhYMehTWL0q6SmtBGdx5S69vYtQxM7M",
    authDomain: "fitnessapp-9dd51.firebaseapp.com",
    databaseURL: "https://fitnessapp-9dd51.firebaseio.com",
    projectId: "fitnessapp-9dd51",
    storageBucket: "fitnessapp-9dd51.appspot.com",
    messagingSenderId: "563891711128"
  };
  firebase.initializeApp(config);

  var name, email, photoUrl, uid, emailVerified, height, yob;
  
  /*
  firebase.auth().createUserWithEmailAndPassword(email, password).catch(function(error) {
	  // Handle Errors here.
	  var errorCode = error.code;
	  var errorMessage = error.message;

	  console.log(errorMessage);
  });
  */

	firebase.auth().onAuthStateChanged(function(user) {
	  if (user) {
	    // User is signed in.
	    console.log("User signed in.");
	    name = user.displayName;
		  email = user.email;
		  photoUrl = user.photoURL;
		  emailVerified = user.emailVerified;
		  uid = user.uid; 

		  var userId = firebase.auth().currentUser.uid;
			firebase.database().ref('/users/' + userId).once('value').then(function(snapshot) {
				yob = (snapshot.val() && snapshot.val().yob) || 3000;
				height = (snapshot.val() && snapshot.val().height) || 1000;
				mobile = (snapshot.val() && snapshot.val().mobile);
				
				console.log(name, email, photoUrl, uid);
			  $('#user').html(' ' + user.displayName);
			  $('#displayname').val(user.displayName);
			  $('#user-image').attr("src", user.photoURL);
			  $('#yob').val(yob);
			  $('#height').val(height);
			  $('#mobile').val(mobile);
			});

		  $('.signedin').show();
		  $('.signedout').hide();

	  } else {
	    // No user is signed in.
	    console.log("User signed out.");

		  $('.signedin').hide();
		  $('.signedout').show();
	  }
	});


</script>
</html>