<?php 

session_start();

if(isset($_POST['register'])){
  
    $data=[     
      'name'=>$_POST['Username'],
      'email'=>$_POST['Email'],
      'password'=>$_POST['Password'],
      'phone'=>$_POST['Mobilenumber'],
          ];
  
    if( !validateForm($data) && validateEmail($data['email']) && 6<=strlen($data['password']) && validateMobile($data['phone'])){
  
      $_SESSION['formData']=$data;  
       
      try{
     
        $conn = new mysqli("localhost", "id21666659_sugan", "Sugansugan28###", "id21666659_database1");
  
         if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
          }else {
     
                  $stmt = $conn->prepare("SELECT * FROM register WHERE email = ?");
                  $stmt->bind_param("s",$_SESSION['formData']['email']); 
                  $stmt->execute();
    
                  $result = $stmt->get_result();
     
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
       
                  $stmt->close();  
                  $conn->close();  
  
          } 
       
      }catch(exception $e){ 
        
            $data['response_message'] ='Internal server problem';
            $data['inputBorder'] ="1px solid #ccc";
            $resultFromServer=json_encode($data);
    
            $htmlContent = file_get_contents('register.html'); 
            echo "<script>var jsonData = " . $resultFromServer . ";</script>";
            echo $htmlContent;
  
            if (isset($stmt)) {
               $stmt->close(); 
            }
  
            if (isset($conn)) {
               $conn->close(); 
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
  
  
  } 
  

if(isset($_POST['login'])){

    $email=$_POST['email'];
    $password=$_POST['password'];

    if(validateEmail($email) && 6<=strlen($password)){

        try{

            $conn = new mysqli("localhost", "id21666659_sugan", "Sugansugan28###", "id21666659_database1");
  
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            } else {

                $stmt = $conn->prepare("SELECT pass FROM register WHERE email = ? ");
                $stmt->bind_param("s",$email); 

                $stmt->execute();

                $stmt->bind_result($hashedPassword);
                $stmt->fetch();

                if($hashedPassword){

                    if (password_verify($password, $hashedPassword)) {  
  
                          $_SESSION['email']=$email;  

                          $htmlContent = file_get_contents('profile.html'); 
                          echo "<script>var data = '" . htmlspecialchars($_SESSION['email'], ENT_QUOTES, 'UTF-8') . "';</script>";
                          echo $htmlContent;

                    } else {  

                            $msg= 'Incorrect email or password';
                            $inputBorder="1px solid red";

                            $resultFromServer=json_encode(['email'=>$email,'password'=>$password,'response_message'=>$msg,'inputBorder'=> $inputBorder]);
  
                            $htmlContent = file_get_contents('Login.html'); 
                            echo "<script>var jsonData = " . $resultFromServer . ";</script>";
                            echo $htmlContent;
                    }

                }else{ 

                    $msg= 'The email has not been registered yet';

                    $inputBorder="1px solid red";

                    $resultFromServer=json_encode(['email'=>$email,'password'=>$password,'response_message'=>$msg,'inputBorder'=> $inputBorder]);
  
                    $htmlContent = file_get_contents('Login.html'); 
                    echo "<script>var jsonData = " . $resultFromServer . ";</script>";
                    echo $htmlContent; 

                    }

                $stmt->close();  
                $conn->close(); 
              }
  
        }catch(exception $e){
          
              $msg= 'Internal server problem';
              $inputBorder="1px solid #ccc";
        
              $resultFromServer=json_encode(['email'=>$email,'password'=>$password,'response_message'=>$msg,'inputBorder'=> $inputBorder]);
          
              $htmlContent = file_get_contents('Login.html'); 
              echo "<script>var jsonData = " . $resultFromServer . ";</script>";
              echo $htmlContent;
              
              if (isset($stmt)) {
                 $stmt->close(); 
              }
              
              if (isset($conn)) {
                 $conn->close(); 
              }
              
            }

    }else{  

          $msg= 'Incorrect email or password';
          $inputBorder="1px solid red";

          $resultFromServer=json_encode(['email'=>$email,'password'=>$password,'response_message'=>$msg,'inputBorder'=> $inputBorder]);
  
          $htmlContent = file_get_contents('Login.html'); 
          echo "<script>var jsonData = " . $resultFromServer . ";</script>";
          echo $htmlContent;
  
        }

}  

if (isset($_GET['users'])) {

    $conn = new mysqli("localhost", "id21666659_sugan", "Sugansugan28###", "id21666659_database1");

    //fetching data from userinfo table on 000webhost.com (free webost service with database connection) 
    $sql="SELECT * FROM register";

    $result = mysqli_query($conn,$sql);

     $arr=[];

    if($result->num_rows){  
        
          while($row=mysqli_fetch_assoc($result)){ 

                echo json_encode(array(
     
                      's_no'=>$row['id'],
                      'date'=>$row['date'],
                      'email'=>$row['email'],
                      'mobile'=>$row['mobile'],
                     ));
                     echo "#";  //for parsing the data in front-end
          }
         
    }else{  

          echo $arr;
      
    }

    $result->close();  
    $conn->close();


}


if(isset($_POST['logout'])){

       session_destroy();
       echo json_encode(['message'=>"sign out successful"]);
  
  }
  
  function encrypt($userProvidedPassword){  // encrypting password for security purposes
  
        $hashedPassword = password_hash($userProvidedPassword, PASSWORD_DEFAULT);
  
        return $hashedPassword;
  
  }
  
  
  function validateForm($arr) { 
      
      foreach ($arr as $key => $value) {
          if ($value =="") {
              return true;
          }
      }
      return false;
  }
  
  
  
  
  function validateEmail($email) {
          $email = filter_var($email, FILTER_SANITIZE_EMAIL);
          return filter_var($email, FILTER_VALIDATE_EMAIL)?true:false;
  }
  
  
  
  function validateMobile($mobile) {
         
          $pattern = '/[789]\d{9}/';
          return preg_match($pattern, $mobile)?true:false;
  
        }
  
  
function insertDataIntoDatabase(){

    $data=$_SESSION['formData'];  

    $hashedPassword =encrypt($data['password']);

    try{

      $conn = new mysqli("localhost", "id21666659_sugan", "Sugansugan28###", "id21666659_database1");


        if ($conn->connect_error) {
              die("Connection failed: " . $conn->connect_error);
        }else {  
     
              $stmt = $conn->prepare("INSERT INTO register (name,email,pass,mobile) VALUES (?,?,?,?)");
              $stmt->bind_param("ssss",$data['name'],$data['email'],$hashedPassword,$data['phone']);

              if ($stmt->execute()) { 

                        $msg= "Successfully registered let's login";

                        $inputBorder="1px solid #ccc";

                        $resultFromServer=json_encode(['email'=>"",'password'=>"",'response_message'=>$msg,'inputBorder'=>$inputBorder]);

                        $htmlContent = file_get_contents('Login.html'); 
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

              $stmt->close(); 
              $conn->close();  

        }

    }catch(exception $e){ 

        $data['response_message'] ='Internal server problem';
        $data['inputBorder'] ="1px solid #ccc";
        $resultFromServer=json_encode($data);

        $htmlContent = file_get_contents('register.html'); 
        echo "<script>var jsonData = " . $resultFromServer . ";</script>";
        echo $htmlContent;

        
        if (isset($stmt)) {
           $stmt->close(); 
        }
        
        if (isset($conn)) {
           $conn->close(); 
         }
     
    }

}


?>
