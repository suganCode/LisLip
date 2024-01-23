<?php 

session_start();

if(isset($_POST['register'])){

    //collecting  registration form data  
  
    $data=[     
      'name'=>$_POST['Username'],
      'email'=>$_POST['Email'],
      'password'=>$_POST['Password'],
      'phone'=>$_POST['Mobilenumber'],
          ];
  
    if( !validateForm($data) && validateEmail($data['email']) && 6<=strlen($data['password']) && validateMobile($data['phone'])){
  
      /// successfully form data validated and granted to register in database
      $_SESSION['formData']=$data; //storing all form data in session 
       
      try{
     
        $conn = new mysqli("localhost", "id21666659_sugan", "Sugansugan28###", "id21666659_database1");
  
         // Check connection 
         if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
          }else {
     
                  $stmt = $conn->prepare("SELECT * FROM register WHERE email = ?");
                  $stmt->bind_param("s",$_SESSION['formData']['email']); // Assuming user_id is an integer; adjust "i" accordingly for other data types
                  // Execute the statement
                  $stmt->execute();
    
                  // Bind the result variables   
                  $result = $stmt->get_result();
     
                  // Get the number of rows
                  if($result->num_rows){  
     
                        $msg= "Already registered let's login"; 
     
                        $inputBorder="1px solid #ccc"; 
     
                        $resultFromServer=json_encode(['email'=>"",'password'=>"",'response_message'=>$msg,'inputBorder'=>$inputBorder]);
       
                        $htmlContent = file_get_contents('Login.html'); //proceed to login page (index.html)
                        echo "<script>var jsonData = " . $resultFromServer . ";</script>";
                        echo $htmlContent;
       
                  }else{  

                    insertDataIntoDatabase();
                  }
       
                  $stmt->close();  //closing stmt
                  $conn->close(); //closing mysqli interface connection 
  
          } // end of else statement 
       
      }catch(exception $e){ //if any execption occurs, catch that exception and again return register.html with entered details
        
            $data['response_message'] ='Internal server problem';
            $data['inputBorder'] ="1px solid #ccc";
            $resultFromServer=json_encode($data);
    
            $htmlContent = file_get_contents('register.html'); 
            echo "<script>var jsonData = " . $resultFromServer . ";</script>";
            echo $htmlContent;
  
            if (isset($stmt)) {
               $stmt->close(); // Close the prepared statement
            }
  
            // Check if $conn is set before closing
            if (isset($conn)) {
               $conn->close(); // Close the mysqli interface connection
            }
  
      }
     
      
    }else{
  
          $data['response_message'] ='Please enter valid information';
          $data['inputBorder'] ="1px solid red";
  
          $resultFromServer=json_encode($data);
    
          $htmlContent = file_get_contents('register.html'); 
          echo "<script>var jsonData = " . $resultFromServer . ";</script>";
          echo $htmlContent;
  
          }
  
  
  }  // end of form registration block
  
  


//block for login process
if(isset($_POST['login'])){

    $email=$_POST['email'];
    $password=$_POST['password'];

    if(validateEmail($email) && 6<=strlen($password)){

        try{

            $conn = new mysqli("localhost", "id21666659_sugan", "Sugansugan28###", "id21666659_database1");
  
            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            } else {

                $stmt = $conn->prepare("SELECT pass FROM register WHERE email = ? ");
                $stmt->bind_param("s",$email); 

                // Execute the statement
                $stmt->execute();

                $stmt->bind_result($hashedPassword);
                $stmt->fetch();

                // Get the number of rows
                if($hashedPassword){

                    if (password_verify($password, $hashedPassword)) {  //check wether the password matched or not
  
                          $_SESSION['email']=$email;  // storing the email to session if login successful
                          //proceed to display profile page //

                          $htmlContent = file_get_contents('profile.html'); 
                          echo "<script>var data = '" . htmlspecialchars($_SESSION['email'], ENT_QUOTES, 'UTF-8') . "';</script>";
                          echo $htmlContent;

                    } else {  

                            //if password not matched
                            $msg= 'Incorrect email or password';
                            $inputBorder="1px solid red";

                            $resultFromServer=json_encode(['email'=>$email,'password'=>$password,'response_message'=>$msg,'inputBorder'=> $inputBorder]);
  
                            $htmlContent = file_get_contents('Login.html'); 
                            echo "<script>var jsonData = " . $resultFromServer . ";</script>";
                            echo $htmlContent;
                    }

                }else{ 

                    //User not yet registered 
                    $msg= 'The email has not been registered yet';

                    $inputBorder="1px solid red";

                    $resultFromServer=json_encode(['email'=>$email,'password'=>$password,'response_message'=>$msg,'inputBorder'=> $inputBorder]);
  
                    $htmlContent = file_get_contents('Login.html'); 
                    echo "<script>var jsonData = " . $resultFromServer . ";</script>";
                    echo $htmlContent; 

                    }

                $stmt->close();  //closing stmt
                $conn->close(); //closing mysqli interface connection 

              }
  
        }catch(exception $e){
          
              $msg= 'Internal server problem';
              $inputBorder="1px solid #ccc";
        
              $resultFromServer=json_encode(['email'=>$email,'password'=>$password,'response_message'=>$msg,'inputBorder'=> $inputBorder]);
          
              $htmlContent = file_get_contents('Login.html'); 
              echo "<script>var jsonData = " . $resultFromServer . ";</script>";
              echo $htmlContent;
              
              if (isset($stmt)) {
                 $stmt->close(); // Close the prepared statement
              }
              // Check if $conn is set before closing
              if (isset($conn)) {
                 $conn->close(); // Close the mysqli interface connection
              }
              
            }

    }else{  // if user doesn't enter correct email or password  as per mentioned

          $msg= 'Incorrect email or password';
          $inputBorder="1px solid red";

          $resultFromServer=json_encode(['email'=>$email,'password'=>$password,'response_message'=>$msg,'inputBorder'=> $inputBorder]);
  
          $htmlContent = file_get_contents('Login.html'); 
          echo "<script>var jsonData = " . $resultFromServer . ";</script>";
          echo $htmlContent;
  
        }

}  //end of login block




if (isset($_GET['users'])) {

    $conn = new mysqli("localhost", "id21666659_sugan", "Sugansugan28###", "id21666659_database1");

    $sql="SELECT * FROM register";
    // Execute the statement

    // Bind the result variables   
    $result = mysqli_query($conn,$sql);

    // Get the number of rows

     $arr=[];

    if($result->num_rows){  
        
          while($row=mysqli_fetch_assoc($result)){ 

                echo json_encode(array(
     
                      's_no'=>$row['id'],
                      'date'=>$row['date'],
                      'email'=>$row['email'],
                      'mobile'=>$row['mobile'],
                     ));
                     echo "#";  //for parsing purpose
          }
         
    }else{  

          echo $arr;
      
    }

    $result->close();  //closing stmt
    $conn->close(); //closing mysqli interface connection 


}


if(isset($_POST['logout'])){

    // Destroy the session
       session_destroy();
       echo json_encode(['message'=>"sign out successful"]);
  
  }
  
  function encrypt($userProvidedPassword){  // encrypting password for security purposes
  
        $hashedPassword = password_hash($userProvidedPassword, PASSWORD_DEFAULT);
  
        return $hashedPassword;
  
  }
  
  
  function validateForm($arr) { //check  wether the array has an empty value or not
      
      foreach ($arr as $key => $value) {
          if ($value =="") {
              return true;
          }
      }
      return false;
  }
  
  
  
  
  function validateEmail($email) {
    // Remove illegal characters from email
          $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    // Validate email address
          return filter_var($email, FILTER_VALIDATE_EMAIL)?true:false;
  }
  
  
  
  function validateMobile($mobile) {
         
          $pattern = '/[789]\d{9}/';
          return preg_match($pattern, $mobile)?true:false;
  
        }
  
  
function insertDataIntoDatabase(){

    $data=$_SESSION['formData']; //fetching data from session 

    $hashedPassword =encrypt($data['password']);

    try{

      $conn = new mysqli("localhost", "id21666659_sugan", "Sugansugan28###", "id21666659_database1");


        // Check connection 
        if ($conn->connect_error) {
              die("Connection failed: " . $conn->connect_error);
        }else {  
     
              $stmt = $conn->prepare("INSERT INTO register (name,email,pass,mobile) VALUES (?,?,?,?)");
              $stmt->bind_param("ssss",$data['name'],$data['email'],$hashedPassword,$data['phone']);

              // Execute the statement 
              if ($stmt->execute()) { 

                        $msg= "Successfully registered let's login";

                        $inputBorder="1px solid #ccc";

                        $resultFromServer=json_encode(['email'=>"",'password'=>"",'response_message'=>$msg,'inputBorder'=>$inputBorder]);

                        $htmlContent = file_get_contents('Login.html'); //proceed to login page (index.html)
                        echo "<script>var jsonData = " . $resultFromServer . ";</script>";
                        echo $htmlContent;


              } else { 

                      $data['response_message'] ='Unable to insert data';
                      $data['inputBorder'] ="1px solid #ccc";
                      $resultFromServer=json_encode($data);

                      $htmlContent = file_get_contents('register.html'); 
                      echo "<script>var jsonData = " . $resultFromServer . ";</script>";
                      echo $htmlContent;

              }

              $stmt->close();  //closing stmt
              $conn->close(); //closing mysqli interface connection 

        }

    }catch(exception $e){ 

        $data['response_message'] ='Internal server problem';
        $data['inputBorder'] ="1px solid #ccc";
        $resultFromServer=json_encode($data);

        $htmlContent = file_get_contents('register.html'); 
        echo "<script>var jsonData = " . $resultFromServer . ";</script>";
        echo $htmlContent;

        
        if (isset($stmt)) {
           $stmt->close(); // Close the prepared statement
        }
        // Check if $conn is set before closing
        if (isset($conn)) {
           $conn->close(); // Close the mysqli interface connection
         }
     
    }

}



?>