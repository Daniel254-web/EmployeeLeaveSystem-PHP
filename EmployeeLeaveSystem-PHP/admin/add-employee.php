<?php
session_start();
error_reporting(0);
include('../includes/dbconn.php');

if(strlen($_SESSION['alogin'])==0){   
    header('location:index.php');
} else {
    if(isset($_POST['add'])){
        // Auto-generate employee ID and password
        function generateRandomString($length = 10) {
            return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
        }

        function generateEmployeeID($dbh) {
            $empid = 'EMP' . strtoupper(generateRandomString(5));
            $sql = "SELECT EmpId FROM tblemployees WHERE EmpId = :empid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':empid', $empid, PDO::PARAM_STR);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return generateEmployeeID($dbh);
            } else {
                return $empid;
            }
        }

        $empid = generateEmployeeID($dbh);
        $password = generateRandomString(12); // Generate a 12-character password

        $fname = $_POST['firstName'];
        $lname = $_POST['lastName'];   
        $email = $_POST['email']; 
        $passwordHash = md5($password); 
        $gender = $_POST['gender']; 
        $dob = $_POST['dob']; 
        $department = $_POST['department']; 
        $mobileno = $_POST['mobileno']; 
        $status = 1;
        
        $sql = "INSERT INTO tblemployees(EmpId,FirstName,LastName,EmailId,Password,Gender,Dob,Department,Phonenumber,Status) 
                VALUES(:empid,:fname,:lname,:email,:password,:gender,:dob,:department,:mobileno,:status)";

        try {
            $query = $dbh->prepare($sql);
            $query->bindParam(':empid', $empid, PDO::PARAM_STR);
            $query->bindParam(':fname', $fname, PDO::PARAM_STR);
            $query->bindParam(':lname', $lname, PDO::PARAM_STR);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->bindParam(':password', $passwordHash, PDO::PARAM_STR);
            $query->bindParam(':gender', $gender, PDO::PARAM_STR);
            $query->bindParam(':dob', $dob, PDO::PARAM_STR);
            $query->bindParam(':department', $department, PDO::PARAM_STR);
            $query->bindParam(':mobileno', $mobileno, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->execute();

            $lastInsertId = $dbh->lastInsertId();
            if($lastInsertId){
                $msg = "Record has been added Successfully. Employee ID: $empid, Password: $password";
            } else {
                $error = "Error adding record";
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
?>

<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Admin Panel - Employee Leave</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/png" href="../assets/images/icon/favicon.ico">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/themify-icons.css">
    <link rel="stylesheet" href="../assets/css/metisMenu.css">
    <link rel="stylesheet" href="../assets/css/owl.carousel.min.css">
    <link rel="stylesheet" href="../assets/css/slicknav.min.css">
    <link rel="stylesheet" href="../assets/css/typography.css">
    <link rel="stylesheet" href="../assets/css/default-css.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <script src="../assets/js/vendor/modernizr-2.8.3.min.js"></script>
    <script type="text/javascript">
        function valid(){
            if(document.addemp.password.value != document.addemp.confirmpassword.value) {
                alert("New Password and Confirm Password Field do not match !!");
                document.addemp.confirmpassword.focus();
                return false;
            }
            return true;
        }
    </script>
    <script>
        function checkAvailabilityEmpid() {
            $("#loaderIcon").show();
            jQuery.ajax({
                url: "check_availability.php",
                data:'empcode='+$("#empcode").val(),
                type: "POST",
                success:function(data){
                    $("#empid-availability").html(data);
                    $("#loaderIcon").hide();
                },
                error:function (){}
            });
        }

        function checkAvailabilityEmailid() {
            $("#loaderIcon").show();
            jQuery.ajax({
                url: "check_availability.php",
                data:'emailid='+$("#email").val(),
                type: "POST",
                success:function(data){
                    $("#emailid-availability").html(data);
                    $("#loaderIcon").hide();
                },
                error:function (){}
            });
        }
    </script>
</head>

<body>
    <div id="preloader">
        <div class="loader"></div>
    </div>
    
    <div class="page-container">
        <div class="sidebar-menu">
            <div class="sidebar-header">
                <div class="logo">
                    <a href="dashboard.php"><img src="../assets/images/icon/logo.png" alt="logo"></a>
                </div>
            </div>
            <div class="main-menu">
                <div class="menu-inner">
                    <?php
                        $page='employee';
                        include '../includes/admin-sidebar.php';
                    ?>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="header-area">
                <div class="row align-items-center">
                    <div class="col-md-6 col-sm-8 clearfix">
                        <div class="nav-btn pull-left">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-4 clearfix">
                        <ul class="notification-area pull-right">
                            <li id="full-view"><i class="ti-fullscreen"></i></li>
                            <li id="full-view-exit"><i class="ti-zoom-out"></i></li>
                            <?php include '../includes/admin-notification.php'?>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="page-title-area">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <div class="breadcrumbs-area clearfix">
                            <h4 class="page-title pull-left">Add Employee Section</h4>
                            <ul class="breadcrumbs pull-left"> 
                                <li><a href="employees.php">Employee</a></li>
                                <li><span>Add</span></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-sm-6 clearfix">
                        <div class="user-profile pull-right">
                            <img class="avatar user-thumb" src="../assets/images/admin.png" alt="avatar">
                            <h4 class="user-name dropdown-toggle" data-toggle="dropdown">Dept Head <i class="fa fa-angle-down"></i></h4>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="logout.php">Log Out</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="main-content-inner">
                <div class="row">
                    <div class="col-lg-6 col-ml-12">
                        <div class="row">
                            <div class="col-12 mt-5">
                            <?php if($error){?>
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <strong>Info: </strong><?php echo htmlentities($error); ?>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            <?php } else if($msg){?>
                                <div class="alert alert-success alert-dismissible fade show">
                                    <strong>Info: </strong><?php echo htmlentities($msg); ?> 
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            <?php } ?>

                            <div class="card">
                                <form name="addemp" method="POST">
                                    <div class="card-body">
                                        <h4 class="header-title">Employee Personal Information</h4>
                                        <p style="font-size:16px; color:red" align="center">
                                            <?php if($msg){ echo $msg; } ?>
                                        </p>

                                        <div class="form-group">
                                            <label for="example-text-input" class="col-form-label">First Name</label>
                                            <input class="form-control" name="firstName" type="text" autocomplete="off" required id="firstName">
                                        </div>

                                        <div class="form-group">
                                            <label for="example-text-input" class="col-form-label">Last Name</label>
                                            <input class="form-control" name="lastName" type="text" autocomplete="off" required id="lastName">
                                        </div>

                                        <div class="form-group">
                                            <label for="example-email-input" class="col-form-label">Email</label>
                                            <input class="form-control" name="email" type="email" autocomplete="off" required id="email" onBlur="checkAvailabilityEmailid()">
                                        </div>

                                        <!-- Password fields removed as password is auto-generated -->
                                        <div class="form-group">
                                            <label class="col-form-label">Gender</label>
                                            <select class="custom-select" name="gender" autocomplete="off">
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="example-date-input" class="col-form-label">Date of Birth</label>
                                            <input class="form-control" name="dob" type="date" id="dob">
                                        </div>

                                        <div class="form-group">
                                            <label class="col-form-label">Department</label>
                                            <select class="custom-select" name="department" autocomplete="off">
                                                <option value="IT">IT</option>
                                                <option value="HR">Grill A</option>
                                                <option value="Finance">Grill B</option>
                                                <option value="Marketing">Farmer</option>
                                                <option value="HR">Container A</option>
                                                <option value="Finance">Container B</option>

                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="mobileno" class="col-form-label">Mobile Number</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <select class="custom-select" id="countryCode" name="countryCode">
                                                    <option value="1">+1</option>
                                                    <option value="1">+1</option>
<option value="93">+93</option>
<option value="355">+355</option>
<option value="213">+213</option>
<option value="1684">+1684</option>
<option value="376">+376</option>
<option value="244">+244</option>
<option value="1264">+1264</option>
<option value="672">+672</option>
<option value="1268">+1268</option>
<option value="54">+54</option>
<option value="374">+374</option>
<option value="297">+297</option>
<option value="61">+61</option>
<option value="43">+43</option>
<option value="994">+994</option>
<option value="1242">+1242</option>
<option value="973">+973</option>
<option value="880">+880</option>
<option value="1246">+1246</option>
<option value="375">+375</option>
<option value="32">+32</option>
<option value="501">+501</option>
<option value="229">+229</option>
<option value="1441">+1441</option>
<option value="975">+975</option>
<option value="591">+591</option>
<option value="387">+387</option>
<option value="267">+267</option>
<option value="55">+55</option>
<option value="246">+246</option>
<option value="1284">+1284</option>
<option value="673">+673</option>
<option value="359">+359</option>
<option value="226">+226</option>
<option value="257">+257</option>
<option value="855">+855</option>
<option value="237">+237</option>
<option value="1">+1</option>
<option value="238">+238</option>
<option value="1345">+1345</option>
<option value="236">+236</option>
<option value="235">+235</option>
<option value="56">+56</option>
<option value="86">+86</option>
<option value="61">+61</option>
<option value="57">+57</option>
<option value="269">+269</option>
<option value="242">+242</option>
<option value="243">+243</option>
<option value="682">+682</option>
<option value="506">+506</option>
<option value="385">+385</option>
<option value="53">+53</option>
<option value="599">+599</option>
<option value="357">+357</option>
<option value="420">+420</option>
<option value="45">+45</option>
<option value="253">+253</option>
<option value="1767">+1767</option>
<option value="1809">+1809</option>
<option value="593">+593</option>
<option value="20">+20</option>
<option value="503">+503</option>
<option value="240">+240</option>
<option value="291">+291</option>
<option value="372">+372</option>
<option value="251">+251</option>
<option value="500">+500</option>
<option value="298">+298</option>
<option value="679">+679</option>
<option value="358">+358</option>
<option value="33">+33</option>
<option value="594">+594</option>
<option value="689">+689</option>
<option value="241">+241</option>
<option value="220">+220</option>
<option value="995">+995</option>
<option value="49">+49</option>
<option value="233">+233</option>
<option value="350">+350</option>
<option value="30">+30</option>
<option value="299">+299</option>
<option value="1473">+1473</option>
<option value="1671">+1671</option>
<option value="502">+502</option>
<option value="224">+224</option>
<option value="245">+245</option>
<option value="592">+592</option>
<option value="509">+509</option>
<option value="504">+504</option>
<option value="852">+852</option>
<option value="36">+36</option>
<option value="354">+354</option>
<option value="91">+91</option>
<option value="62">+62</option>
<option value="98">+98</option>
<option value="964">+964</option>
<option value="353">+353</option>
<option value="44">+44</option>
<option value="972">+972</option>
<option value="39">+39</option>
<option value="1876">+1876</option>
<option value="81">+81</option>
<option value="962">+962</option>
<option value="7">+7</option>
<option value="254">+254</option>
<option value="686">+686</option>
<option value="383">+383</option>
<option value="965">+965</option>
<option value="996">+996</option>
<option value="856">+856</option>
<option value="371">+371</option>
<option value="961">+961</option>
<option value="266">+266</option>
<option value="231">+231</option>
<option value="218">+218</option>
<option value="423">+423</option>
<option value="370">+370</option>
<option value="352">+352</option>
<option value="853">+853</option>
<option value="389">+389</option>
<option value="261">+261</option>
<option value="265">+265</option>
<option value="60">+60</option>
<option value="960">+960</option>
<option value="223">+223</option>
<option value="356">+356</option>
<option value="692">+692</option>
<option value="222">+222</option>
<option value="230">+230</option>
<option value="262">+262</option>
<option value="52">+52</option>
<option value="691">+691</option>
<option value="373">+373</option>
<option value="377">+377</option>
<option value="976">+976</option>
<option value="382">+382</option>
<option value="1664">+1664</option>
<option value="212">+212</option>
<option value="258">+258</option>
<option value="95">+95</option>
<option value="264">+264</option>
<option value="674">+674</option>
<option value="977">+977</option>
<option value="31">+31</option>
<option value="599">+599</option>
<option value="687">+687</option>
<option value="64">+64</option>
<option value="505">+505</option>
<option value="227">+227</option>
<option value="234">+234</option>
<option value="683">+683</option>
<option value="672">+672</option>
<option value="1670">+1670</option>
<option value="47">+47</option>
<option value="968">+968</option>
<option value="92">+92</option>
<option value="680">+680</option>
<option value="970">+970</option>
<option value="507">+507</option>
<option value="675">+675</option>
<option value="595">+595</option>
<option value="51">+51</option>
<option value="63">+63</option>
<option value="64">+64</option>
<option value="48">+48</option>
<option value="351">+351</option>
<option value="1787">+1787</option>
<option value="974">+974</option>
<option value="242">+242</option>
<option value="40">+40</option>
<option value="7">+7</option>
<option value="250">+250</option>
<option value="590">+590</option>
<option value="290">+290</option>
<option value="1869">+1869</option>
<option value="1758">+1758</option>
<option value="590">+590</option>
<option value="508">+508</option>
<option value="1784">+1784</option>
<option value="685">+685</option>
<option value="378">+378</option>
<option value="239">+239</option>
<option value="966">+966</option>
<option value="221">+221</option>
<option value="381">+381</option>
<option value="248">+248</option>
<option value="232">+232</option>
<option value="65">+65</option>
<option value="1721">+1721</option>
<option value="421">+421</option>
<option value="386">+386</option>
<option value="677">+677</option>
<option value="252">+252</option>
<option value="27">+27</option>
<option value="211">+211</option>
<option value="34">+34</option>
<option value="94">+94</option>
<option value="249">+249</option>
<option value="597">+597</option>
<option value="47">+47</option>
<option value="268">+268</option>
<option value="46">+46</option>
<option value="41">+41</option>
<option value="963">+963</option>
<option value="886">+886</option>
<option value="992">+992</option>
<option value="255">+255</option>
<option value="66">+66</option>
<option value="670">+670</option>
<option value="228">+228</option>
<option value="690">+690</option>
<option value="676">+676</option>
<option value="1868">+1868</option>
<option value="216">+216</option>
<option value="90">+90</option>
<option value="993">+993</option>
<option value="1649">+1649</option>
<option value="688">+688</option>
<option value="1340">+1340</option>
<option value="256">+256</option>
<option value="380">+380</option>
<option value="971">+971</option>
<option value="44">+44</option>
<option value="598">+598</option>
<option value="998">+998</option>
<option value="678">+678</option>
<option value="58">+58</option>
<option value="84">+84</option>
<option value="681">+681</option>
<option value="967">+967</option>
<option value="260">+260</option>
<option value="263">+263</option>
<option value="254">+254</option>

        </select>
            </div>
                 <input type="tel" class="form-control" id="mobileno" name="mobileno" autocomplete="off" required>
                     </div>
                     </div>

                        <button class="btn btn-primary" name="add" type="submit">Submit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/vendor/jquery-2.2.4.min.js"></script>
    <script src="../assets/js/popper.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/owl.carousel.min.js"></script>
    <script src="../assets/js/metisMenu.min.js"></script>
    <script src="../assets/js/jquery.slimscroll.min.js"></script>
    <script src="../assets/js/jquery.slicknav.min.js"></script>
    <script src="../assets/js/plugins.js"></script>
    <script src="../assets/js/scripts.js"></script>
    <script>
        $(document).ready(function() {
            $("#mobileno").intlTelInput({
                separateDialCode: true,
                initialCountry: "auto",
                geoIpLookup: function(callback) {
                    $.get("https://ipinfo.io", function() {}, "jsonp").always(function(resp) {
                        var countryCode = (resp && resp.country) ? resp.country : "";
                        callback(countryCode);
                    });
                },
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
            });
        });
    </script>
</body>
</html>

<?php } ?>
