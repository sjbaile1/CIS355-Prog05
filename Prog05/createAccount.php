<?php
session_start();
require "database.php";
$errorMessage = '';
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET["errorMessage"])) $errorMessage = $_GET["errorMessage"];
    echo '<div class="container">
    <h1>Join</h1>
    <form onsubmit="return loadDoc(\'createAccount.php\', \'POST\', this)">
        <img id=imgDisplay overflow=hidden width=200 height=200 src=""/><br>
        <input type="file" name="Filename" onchange="readURL(this);" required><br>
        Description: <br><input name="description" type="text" placeholder="description" required><br>
        Name: <br><input name="name" type="text" placeholder="name" required><br>
        Email: <br><input name="email" type="text" placeholder="me@email.com" required><br>
        Mobile (123-456-7890): <br><input name="mobile" type="tel" placeholder="123-456-7890" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" required><br>
        Password: <br><input name="password" type="password" placeholder="password" required><br>
        <button type="submit" class="btn btn-success">Join</button>
    </form>
</div>';
}
else
    $errorMessage='';
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    // Create an account with the data given from the post.
    $name = $_POST['name'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $password = MD5 ($_POST['password']);
    $description = $_POST['description'];
    $fileName = $_FILES['Filename']['name'];
    $tempFileName = $_FILES['Filename']['tmp_name'];
    $fileSize = $_FILES['Filename']['size'];
    $fileType = $_FILES['Filename']['type'];
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // put the content of the file into a variable, $content
    $fp      = fopen($tempFileName, 'r');
    $content = fread($fp, filesize($tempFileName));
    fclose($fp);
    // Add the data to the database.
    $sql = "INSERT INTO customers (name,email,mobile, password_hash, filename, filetype,content,filesize,description) values(?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $q = $pdo->prepare($sql);
    $q->execute(array($name,$email,$mobile, $password, $fileName, $fileType, $content, $fileSize, $description));
    // Now try to query that username / password combination to make sure the account was created successfully.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "SELECT * FROM customers WHERE email = ? AND password_hash = ? LIMIT 1";
    $q = $pdo->prepare($sql);
    $q->execute(array($email,$password));
    $data = $q->fetch(PDO::FETCH_ASSOC);
    // If we got data back, the account was created successfully. Go to customer.php.
    if ($data) {
        $id = $data["id"];
        $fileLocation = "uploads1/" . $id ."/";
        $fileFullPath = $fileLocation . $fileName;
        if (!file_exists($fileLocation))
            mkdir ($fileLocation, 0777, true); // create subdirectory, if necessary
        else
            array_map('unlink', glob($fileLocation . "*"));
        move_uploaded_file($tempFileName, $fileFullPath);
        chmod($fileFullPath, 0777);
        $absolutePath = realpath($fileFullPath);
        $sql = "UPDATE customers  set absolutepath = ? WHERE id = ?";
        $q = $pdo->prepare($sql);
        $q->execute(array($absolutePath, $id));
        Database::disconnect();
        $_SESSION["username"] = $email;
        header('Content-Type: application/json');
        echo json_encode(['location'=>'customer.html']);
        exit();
    } else { // Otherwise, try creating an account in again.
        Database::disconnect();
        header('Content-Type: application/json');
        echo json_encode(['location'=>'createAccount.html?errorMessage=Something went wrong. Please try again.']);
        exit();
    }
}
?>