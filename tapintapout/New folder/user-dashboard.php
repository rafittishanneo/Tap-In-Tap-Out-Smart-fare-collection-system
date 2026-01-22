<?php
session_start();

// Debugging: Uncomment the next 2 lines if it still fails to see what the role actually is
// var_dump($_SESSION); exit();

// Check if user is logged in
if (!isset($_SESSION['userid']) || !isset($_SESSION['userrole'])) {
    header("Location: login.php");
    exit();
}

// Check if the role is passenger/user
// If the role is NOT 'user', redirect them to the correct dashboard or login
if ($_SESSION['userrole'] !== 'user') {
    // Optional: Redirect admins to admin dashboard if they accidentally land here
    if ($_SESSION['userrole'] === 'admin') {
        header("Location: admin-dashboard.php");
        exit();
    }
    // Otherwise send back to login
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'] ?? 'Passenger';
$useremail = $_SESSION['useremail'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Passenger Dashboard | Tap in Tap Out</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Poppins',sans-serif;background:linear-gradient(135deg,#667eea 0,#764ba2 100%);min-height:100vh;padding:1rem}
        .dashboard{max-width:1200px;margin:0 auto;background:white;border-radius:24px;overflow:hidden;box-shadow:0 30px 60px rgba(0,0,0,0.15)}
        .header{background:linear-gradient(135deg,#2563eb,#1d4ed8);color:white;padding:2rem;position:relative;overflow:hidden}
        .header::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#facc15,transparent)}
        .header-content{display:flex;justify-content:space-between;align-items:center;max-width:1000px;margin:0 auto}
        .welcome{font-size:1.8rem;font-weight:700}
        .user-info{text-align:right}
        .user-email{font-size:0.9rem;opacity:0.9}
        .logout{background:rgba(255,255,255,0.2);color:white;padding:0.6rem 1.5rem;border-radius:999px;text-decoration:none;font-weight:500;border:1px solid rgba(255,255,255,0.3);transition:all 0.3s;display:inline-block;margin-top:0.5rem}
        .logout:hover{background:rgba(255,255,255,0.3);transform:translateY(-2px)}
        .content{padding:2.5rem;max-width:1000px;margin:0 auto}
        .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.5rem;margin-bottom:2.5rem}
        .stat-card{background:white;border-radius:20px;padding:1.8rem;box-shadow:0 10px 30px rgba(0,0,0,0.08);transition:transform 0.3s}
        .stat-card:hover{transform:translateY(-8px)}
        .stat-icon{width:60px;height:60px;border-radius:16px;display:flex;align-items:center;justify-content:center;margin-bottom:1rem;font-size:1.5rem;color:white}
        .wallet .stat-icon{background:linear-gradient(135deg,#10b981,#059669)}
        .balance .stat-icon{background:linear-gradient(135deg,#f59e0b,#d97706)}
        .trips .stat-icon{background:linear-gradient(135deg,#2563eb,#1d4ed8)}
        .stat-number{font-size:2.2rem;font-weight:700;color:#0f172a;margin-bottom:0.3rem}
        .stat-label{color:#64748b;font-weight:500}
        .quick-actions{display:flex;flex-wrap:wrap;gap:1rem;margin-bottom:2.5rem}
        .btn{padding:1rem 2rem;border-radius:16px;font-weight:600;text-decoration:none;transition:all 0.3s;display:inline-flex;align-items:center;gap:0.8rem;font-size:0.95rem;cursor:pointer}
        .btn-primary{background:linear-gradient(135deg,#10b981,#059669);color:white;box-shadow:0 10px 25px rgba(16,185,129,0.3)}
        .btn-primary:hover{transform:translateY(-4px);box-shadow:0 15px 35px rgba(16,185,129,0.4)}
        .btn-outline{background:transparent;border:2px solid #2563eb;color:#2563eb}
        .btn-outline:hover{background:#2563eb;color:white}
        .recent-journeys{background:white;border-radius:20px;padding:2rem;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.08)}
        .section-title{font-size:1.4rem;font-weight:600;color:#0f172a;margin-bottom:1.5rem}
        
        @media(max-width:768px){
            .header-content,.quick-actions{flex-direction:column;gap:1rem;text-align:center}
            .user-info{text-align:center}
            .stats-grid{grid-template-columns:1fr}
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <header class="header">
            <div class="header-content">
                <div class="welcome">
                    Welcome back, <strong><?php echo htmlspecialchars($username); ?>!</strong>
                </div>
                <div class="user-info">
                    <div class="user-email"><?php echo htmlspecialchars($useremail); ?></div>
                    <a href="logout.php" class="logout">Logout</a>
                </div>
            </div>
        </header>

        <main class="content">
            <div class="stats-grid">
                <div class="stat-card wallet">
                    <div class="stat-icon">💳</div>
                    <div class="stat-number">$45.20</div>
                    <div class="stat-label">Wallet Balance</div>
                </div>
                <div class="stat-card balance">
                    <div class="stat-icon">🚌</div>
                    <div class="stat-number">1 Active</div>
                    <div class="stat-label">Ongoing Journey</div>
                </div>
                <div class="stat-card trips">
                    <div class="stat-icon">📍</div>
                    <div class="stat-number">12</div>
                    <div class="stat-label">Total Trips</div>
                </div>
            </div>

            <div class="quick-actions">
                <a href="#" class="btn btn-primary">🚀 Tap In</a>
                <a href="#" class="btn btn-primary">🛑 Tap Out</a>
                <a href="#" class="btn btn-outline">📊 Journey History</a>
                <a href="#" class="btn btn-outline">🔄 Route Change</a>
            </div>

            <section class="recent-journeys">
                <h2 class="section-title">Recent Journeys</h2>
                <div style="display:grid;gap:1rem;">
                    <div style="display:flex;justify-content:space-between;padding:1.2rem;background:#f8fafc;border-radius:12px;border-left:4px solid #10b981;align-items:center;">
                        <div>
                            <div style="font-weight:600;color:#0f172a;">Uttara → Gulshan</div>
                            <div style="color:#64748b;font-size:0.9rem;">2 stops • 25 min ago</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:1.2rem;font-weight:700;color:#10b981;">$2.50</div>
                            <div style="color:#64748b;font-size:0.8rem;">Completed</div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
