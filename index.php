<?php
    include_once "connection.php";
?>
<html>
    <head>
        <title>Library</title>
    </head>
    <body>
        <p>PLEASE SELECT AN OPTION</p>
        <select onchange="window.location = this.options[this.selectedIndex].value;">
             <option>-Select-</option>
            <option value="index.php?action=list">List All Book</option>
            <option value="index.php?action=issue">Issue a Book</option>
            <option value="index.php?action=return">Return a Book</option>
            <option value="index.php?action=admin">Admin Panel</option>
        </select>
        <?php
        $error="";
        $success="";
        //This is for issuing a book
        if(isset($_POST['bookname']) && $_POST['bookname']!='' && isset($_POST['copies']) && $_POST['copies']!='' && isset($_POST['author']) && $_POST['author']!=''){
            $bookname=$_POST['bookname'];
            $author=$_POST['author'];
            $copies=$_POST['copies'];
            $query="INSERT INTO books(name,author,copies) VALUES($bookname,$author,$copies)";
            if(!mysqli_query($link, $query))
            {
                $error= 'Error in inserting.';
            }
            else
            {
                $success= 'A book has been added succefully';
            }
            
        }
         if(isset($_POST['userid']) && $_POST['userid']!='' && isset($_POST['type']) && $_POST['type']!='' && isset($_POST['avail']) && $_POST['avail']!='' ){
            $userid=$_POST['userid'];
            $avail=$_POST['avail'];
            $type=$_POST['type'];
            $query="INSERT INTO users(userid,type,canissue,issued) VALUES($userid,$type,$avail,0)";
            if(!mysqli_query($link, $query))
            {
                $error= 'Error in inserting.';
            }
            else
            {
                $success= 'A user has been added succefully';
            }
        }
        if(isset($_POST['bookid']) && $_POST['bookid']!='' && isset($_POST['userid']) && $_POST['userid']!='' && isset($_POST['days']) && $_POST['days']!=''  && isset($_POST['status']) && $_POST['status']!=''){
            $bookid=$_POST['bookid'];
            $userid=$_POST['userid'];
            $days=$_POST['days'];
            $status=_POST['status'];
            $date=date("dd-mm-yyyy HH:mm:ss");
            $query="SELECT copies FROM books WHERE bookid=".$bookid;
            $copies=  mysqli_query($link, $query);
            $copies=  mysqli_fetch_array($copies);
            if($copies['copies']<=0)
            {
                $error="Books are not available.";
                header("location:index.php?action=issue");
                
            }
            $query="SELECT canissue FROM users WHERE userid=".$userid;
            $no=  mysqli_query($link, $query);
            $can=  mysqli_fetch_array($no);
            if($can['canissue']<=0)
            {
                $error="Books can not be issued to this user";
                header("location:index.php?action=issue");
                
            }
            $query="INSERT INTO issued('bookid','userid','date',days','status') values($bookid,$userid,$date,$days,$status)";
            if(!mysqli_query($link, $query))
            {
               
                $error="Error in issueing";
                header("location:index.php?action=issue");
            }
           
            $query="UPDATE books SET copies=copies-1 WHERE bookid=".$bookid;
            if(!mysqli_query($link, $query))
            {
                $error= 'Error in issueing updating';
            }
            else {
                $success="Book has been issued successfully.";
            }
            $query="UPDATE users SET canissue=canissue-1 AND issued=issued-1 WHERE userid=".$userid;
            if(!mysqli_query($link, $query))
            {
                $error= 'Error in issueing';
            }
             header("location:index.php?action=issue");
        }
        //This is for returning book
        if(isset($_POST['bookid']) && $_POST['bookid']!='' && isset($_POST['userid']) &&$_POST['userid']!=''){
            $bookid=$_POST['bookid'];
            $userid=$_POST['userid'];
            $query="DELETE FROM issued WHERE bookid=".$bookid."AND userid=".$userid;
            if(!mysqli_query($link, $query))
            {
                $error='Not issued to this user';
                header("location:index.php?action=return");
            }
            $query="UPDATE users SET canissue=canissue+1 AND issued=issued-1 WHERE userid=".$userid;
            if(!mysqli_query($link, $query))
            {
                $error= 'Error in issueing';
            }
            $query="UPDATE books SET copies=copies+1 WHERE bookid=".$bookid;
            if(!mysqli_query($link, $query))
            {
                $error= 'Error in issueing';
            }
            else {
                $success="Book has been returned successfully.";
            }
             header("location:index.php?action=return");
        }

        if(isset($_GET['action']) && $_GET['action']!=''){
            $action=$_GET['action'];
            
            if($action=='list')
            {
                $query="SELECT * FROM books";
                $books=  mysqli_query($link, $query);
                
                $html="<table>"
                        . "<thead>"
                        . "<tr>"
                        . "<td></td>"
                        . "<td></td>"
                        . "<td></td>"
                        . "<td></td>"
                        . "</tr>"
                        . "</thead>";
                echo $html;
                while($book=mysqli_fetch_array($books)){
        ?>
             <tr>
                        <td><?=$book['id']?></td>
                       <td><?=$book['name']?></td>
                        <td><?=$book['author']?></td>
                        <td><?=$book['copies']?></td>
            </tr>
                <?php }
            }
        else if($action=='issue'){
            echo $error;
            echo $error;
            ?>
            <br /><br /><br />
            <span>Issue A Book</span>
                <table>
            <form action="index.php" method="post">
              <tr><td>  Enter Book Id</td><td><input type="number" min="1" name="bookid"></td></tr>
                <tr><td>Enter User Id</td><td><input type="number" name="userid" placeholder="Ex-20130154"></td></tr>
               <tr><td>Enter Number of Days</td><td><input type="number" name="days"></td></tr>
               <tr><td>Enter Status</td><td><input type="text" name="status"></td></tr>
               <tr><td></td><td><input type="submit" name="submit" value="Issue"></td></tr>
            </form>
                </table>
                
               <?php }
                   else if($action=='return'){
                        echo $error;
            echo $success;          
                       ?>
            <br /><br /><br />
            <span>Return A Book</span>
                <table>
            <form action="index.php" method="post">
              <tr><td>  Enter Book Id</td><td><input type="number" min="1" name="bookid"></td></tr>
              
               <tr><td>Enter User Id</td><td><input type="number" name="userid" placeholder="Ex-20130154"></td></tr>
               <tr><td></td><td><input type="submit" name="submit" value="Return"></td></tr>
               <!--<tr><td>Enter Number of Days</td><td><input type="number" name="days"></td></tr>
               <tr><td>Enter Status</td><td><input type="text" name="status"></td></tr>-->
            </form>
                </table>
                
               <?php }
               else if($action=='admin')
               {
                    echo $error;
            echo $success;
                   ?>
            <br /><br /><br />
            <span>ADD A BOOK</span>
            <table>
                  <form action="index.php" method="post">
              <tr><td>  Enter Book Name</td><td><input type="text" min="1" name="bookname"></td></tr>
              <tr><td>  Enter Book Author</td><td><input type="text" min="1" name="auther"></td></tr>
              <tr><td>  Enter Book Copies</td><td><input type="number" min="1" name="copies"></td></tr>
               <tr><td></td><td><input type="submit" name="submit" value="Add Book"></td></tr>
            </form>
                </table>
                <span>ADD A USER</span>
                <table>
                  <form action="index.php" method="post">
              <tr><td>  Enter User ID</td><td><input type="number" min="1" name="userid"></td></tr>
              <tr><td>  Enter Type</td><td><input type="text" min="1" name="type"></td></tr>
              <tr><td>  Enter No Books To Avail</td><td><input type="number" min="1" name="avail"></td></tr>
               <tr><td></td><td><input type="submit" name="submit" value="Add User"></td></tr>
            </form>
                </table>
            <?php
               }
              
               ?>
                
            
        <?php }
         else
                   header("location:index.php?action=list");
        ?>
    </body>
</html>