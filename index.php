<?php
session_start();
$loggedIn = isset($_SESSION['user_id']);
include 'db_connect.php';

if (isset($_POST['submit_contact'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $sql = "INSERT INTO contact_messages (name, email, subject, message)
            VALUES ('$name', '$email', '$subject', '$message')";

    mysqli_query($conn, $sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>BloodBuddy</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="styles.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
body { 
    font-family: 'Poppins', sans-serif; 
    margin:0; 
    background:#f8f9fa; 
}

/* HEADER */
header { 
    background:#b30000; 
    color:white; 
    padding:20px 0; 
    text-align:center;
}

header h1{
    margin:0;
    font-size:28px;
}

/* CENTER NAVIGATION */
nav ul { 
    list-style:none; 
    display:flex; 
    justify-content:center;
    align-items:center;
    gap:40px; 
    padding:0;
    margin-top:15px;
}

nav a { 
    color:white; 
    text-decoration:none; 
    font-weight:500; 
    font-size:16px;
}

nav a:hover{
    text-decoration:underline;
}

/* HERO SECTION */
.hero { 
    padding:100px 20px; 
    text-align:center; 
    background:#ffe6e6; 
}

.hero h2{
    color:black;
    font-size:40px;
}

.hero p{
    color:black;
    font-size:18px;
}

.btn { 
    background:#d9534f; 
    color:white; 
    padding:12px 25px; 
    border-radius:30px; 
    text-decoration:none; 
}

section { padding:60px 20px; }

.card {
    background:white;
    max-width:500px;
    margin:auto;
    padding:30px;
    border-radius:12px;
    box-shadow:0 8px 20px rgba(0,0,0,0.1);
}

select, button, input, textarea {
    width:100%;
    padding:12px;
    margin:10px 0;
    border-radius:6px;
    border:1px solid #ccc;
}

button {
    background:#d9534f;
    color:white;
    font-size:16px;
    cursor:pointer;
    border:none;
}
button:hover { background:#c9302c; }

.ai-predict-section {
    max-width:700px;
    margin:auto;
    padding:35px;
    text-align:center;
    background:linear-gradient(135deg,#ffe6e6,#fff);
    border-radius:15px;
}

.ai-btn {
    display:inline-block;
    margin-top:20px;
    padding:14px 28px;
    background:linear-gradient(135deg,#ff4d4d,#b30000);
    color:white;
    border-radius:30px;
    text-decoration:none;
    font-weight:bold;
}
.ai-btn:hover { transform:scale(1.05); }

#chatbot-btn {
    position:fixed; bottom:20px; right:20px;
    background:#d9534f; color:white;
    padding:15px; border-radius:50%;
    cursor:pointer;
}
#chatbot {
    display:none; position:fixed;
    bottom:80px; right:20px;
    width:300px; background:white;
    border-radius:10px; box-shadow:0 0 10px #0003;
}
#chat-header { background:#d9534f; color:white; padding:10px; }
#chat-body { height:250px; overflow-y:auto; padding:10px; }
.user { text-align:right; }
.bot { color:#d9534f; }
</style>

<script>
const districtsByState = {
    "Maharashtra": ["Mumbai","Pune","Nagpur","Nashik"]
};
function updateDistricts(){
    let s=document.getElementById("state").value;
    let d=document.getElementById("district");
    d.innerHTML="";
    districtsByState[s].forEach(x=>{
        let o=document.createElement("option");
        o.value=o.textContent=x;
        d.appendChild(o);
    });
}
</script>
</head>

<body>

<header>
<h1>BloodBuddy</h1>
<nav>
<ul>
<li><a href="#home">Home</a></li>
<li><a href="#donors">Find Donor</a></li>
<li><a href="#contact">Contact</a></li>
<li><a href="dregister.php">Donate</a></li>
<li>
<?php if($loggedIn): ?>
<a href="logout.php">Logout</a>
<?php else: ?>
<a href="login.html">Login</a>
<?php endif; ?>
</li>
</ul>
</nav>
</header>

<section id="home" class="hero">
<h2>Donate Blood, Save Lives ❤️</h2>
<p>Your blood can be someone’s miracle.</p>
<a href="#donors" class="btn">Find Donor</a>
</section>

<section id="about">
<div class="ai-predict-section">
<h3>🔮 AI Blood Demand Prediction</h3>
<p>Machine Learning based prediction to help hospitals prepare in advance.</p>
<a href="predict_demand.php" class="ai-btn">📊 Predict Blood Demand (AI)</a>
</div>
</section>

<section id="donors">
<h2 style="text-align:center;">Find a Blood Donor</h2>

<div class="card">
<form action="find_donor.php" method="POST">

<select name="blood_group" required>
<option value="">Select Blood Group</option>
<option>A+</option><option>A-</option>
<option>B+</option><option>B-</option>
<option>AB+</option><option>AB-</option>
<option>O+</option><option>O-</option>
</select>

<select name="state" id="state" onchange="updateDistricts()" required>
<option value="">Select State</option>
<option value="Maharashtra">Maharashtra</option>
</select>

<select name="district" id="district" required>
<option value="">Select District</option>
</select>

<label style="color:#b30000;font-weight:bold;">
<input type="checkbox" name="emergency" value="1" checked>
 🚨 Emergency (AI Priority Mode)
</label>

<button type="submit">🔍 Search Donor (AI)</button>

</form>
</div>
</section>

<section id="contact">
<h2 style="text-align:center;">Contact Us</h2>
<div class="card">
<form method="POST">
<input name="name" placeholder="Name" required>
<input name="email" placeholder="Email" required>
<input name="subject" placeholder="Subject">
<textarea name="message" placeholder="Message" required></textarea>
<button name="submit_contact">Send</button>
</form>
</div>
</section>

<footer style="text-align:center;padding:20px;">
<p>&copy; 2025 BloodBuddy</p>
</footer>

<div id="chatbot-btn" onclick="toggleChat()">💬</div>
<div id="chatbot">
<div id="chat-header">Blood AI 🤖</div>
<div id="chat-body"></div>
<input id="chat-input" placeholder="Type..." onkeypress="if(event.key==='Enter')sendMsg()">
</div>

<script>
function toggleChat(){
let c=document.getElementById("chatbot");
c.style.display=c.style.display==="block"?"none":"block";
}
function sendMsg(){
let i=document.getElementById("chat-input");
let m=i.value.trim();
if(!m)return;
document.getElementById("chat-body").innerHTML+=`<div class='user'>${m}</div>`;
i.value="";
fetch("chatbot.php",{method:"POST",
headers:{"Content-Type":"application/x-www-form-urlencoded"},
body:"message="+encodeURIComponent(m)})
.then(r=>r.text()).then(t=>{
document.getElementById("chat-body").innerHTML+=`<div class='bot'>${t}</div>`;
});
}
</script>

</body>
</html>
