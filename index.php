<?php
require_once 'includes/header.php';

$upcoming_events = getUpcomingEvents(6);
$stats = getEventStats();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center justify-content-center text-center text-lg-start">
            <div class="col-lg-6 mb-5 mb-lg-0" data-aos="fade-right">
                <h1 class="display-4 fw-bold mb-3">Welcome to <span class="text-gradient">Mukono Community Church</span></h1>
                <p class="lead mb-4">Smart Church Event Scheduler – Efficiently coordinate, manage, and participate in church events.</p>
                <div class="d-flex gap-3 flex-wrap justify-content-center justify-content-lg-start">
                    <?php if (!$auth->isLoggedIn()): ?>
                        <a href="register.php" class="btn btn-primary btn-lg px-4 py-3">Join Us Today <i class="fas fa-arrow-right ms-2"></i></a>
                        <a href="events.php" class="btn btn-outline-light btn-lg px-4 py-3">Browse Events</a>
                    <?php else: ?>
                        <a href="member/create-event.php" class="btn btn-primary btn-lg px-4 py-3">Create an Event <i class="fas fa-plus-circle ms-2"></i></a>
                        <a href="member/dashboard.php" class="btn btn-outline-light btn-lg px-4 py-3">My Dashboard</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="hero-image text-center">
                    <img src="assets/images/chuch illustration.jpg" alt="Church Illustration" class="img-fluid hero-img">
                </div>
            </div>
        </div>
    </div>
    <div class="hero-wave">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="#ffffff" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section py-5">
    <div class="container">
        <div class="row g-4 justify-content-center">
            <div class="col-md-3 col-sm-6" data-aos="zoom-in" data-aos-delay="100">
                <div class="stat-card text-center p-4">
                    <div class="stat-icon mb-3">
                        <i class="fas fa-calendar-alt fa-3x text-primary"></i>
                    </div>
                    <h3 class="stat-number"><?php echo isset($stats['total_events']) ? $stats['total_events'] : 0; ?></h3>
                    <p class="stat-label mb-0">Total Events</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6" data-aos="zoom-in" data-aos-delay="200">
                <div class="stat-card text-center p-4">
                    <div class="stat-icon mb-3">
                        <i class="fas fa-check-circle fa-3x text-success"></i>
                    </div>
                    <h3 class="stat-number"><?php echo isset($stats['approved_events']) ? $stats['approved_events'] : 0; ?></h3>
                    <p class="stat-label mb-0">Approved Events</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6" data-aos="zoom-in" data-aos-delay="300">
                <div class="stat-card text-center p-4">
                    <div class="stat-icon mb-3">
                        <i class="fas fa-clock fa-3x text-warning"></i>
                    </div>
                    <h3 class="stat-number"><?php echo isset($stats['pending_events']) ? $stats['pending_events'] : 0; ?></h3>
                    <p class="stat-label mb-0">Pending Events</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6" data-aos="zoom-in" data-aos-delay="400">
                <div class="stat-card text-center p-4">
                    <div class="stat-icon mb-3">
                        <i class="fas fa-users fa-3x text-info"></i>
                    </div>
                    <h3 class="stat-number"><?php echo isset($stats['total_members']) ? $stats['total_members'] : 0; ?></h3>
                    <p class="stat-label mb-0">Church Members</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="section-title">Why Choose Our Platform?</h2>
            <p class="section-subtitle">Smart tools to enhance your church experience</p>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-card text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-calendar-check fa-3x text-primary"></i>
                    </div>
                    <h4>Easy Event Scheduling</h4>
                    <p>Create, manage, and promote church events with just a few clicks. No technical skills required.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-card text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-bell fa-3x text-primary"></i>
                    </div>
                    <h4>Smart Notifications</h4>
                    <p>Get instant reminders and updates about upcoming events. Never miss an important gathering.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-card text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-chart-line fa-3x text-primary"></i>
                    </div>
                    <h4>Attendance Tracking</h4>
                    <p>Monitor event participation and manage registrations efficiently. Generate detailed reports.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Upcoming Events Section -->
<section class="events-section py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="section-title">Upcoming Events</h2>
            <p class="section-subtitle">Join us in fellowship and worship</p>
        </div>
        <div class="row justify-content-center">
            <?php if ($upcoming_events && $upcoming_events->num_rows > 0): ?>
                <?php while ($event = $upcoming_events->fetch_assoc()): ?>
                    <div class="col-lg-4 col-md-6 mb-4 d-flex justify-content-center" data-aos="fade-up" data-aos-delay="<?php echo $event['id'] * 100; ?>">
                        <div class="event-card h-100 w-100">
                            <div class="event-date-badge">
                                <span class="day"><?php echo date('d', strtotime($event['event_date'])); ?></span>
                                <span class="month"><?php echo date('M', strtotime($event['event_date'])); ?></span>
                            </div>
                            <?php if (isset($event['image']) && !empty($event['image']) && file_exists($event['image'])): ?>
                                <img src="<?php echo $event['image']; ?>" class="event-card-img" alt="<?php echo htmlspecialchars($event['title']); ?>">
                            <?php else: ?>
                                <img src="assets/images/default-event.jpg" class="event-card-img" alt="Default Event Image">
                            <?php endif; ?>
                            <div class="event-card-body">
                                <h5 class="event-card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                <div class="event-meta">
                                    <span><i class="fas fa-clock"></i> <?php echo date('g:i A', strtotime($event['start_time'])); ?> - <?php echo date('g:i A', strtotime($event['end_time'])); ?></span>
                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                                </div>
                                <p class="event-card-text"><?php echo substr(htmlspecialchars($event['description']), 0, 100); ?>...</p>
                                <a href="event-details.php?id=<?php echo $event['id']; ?>" class="btn btn-outline-primary btn-sm">View Details <i class="fas fa-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="empty-state">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                        <h4>No upcoming events at the moment</h4>
                        <p>Check back soon for exciting church events and activities!</p>
                        <?php if (!$auth->isLoggedIn()): ?>
                            <a href="register.php" class="btn btn-primary mt-2">Register to Create Events</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($upcoming_events && $upcoming_events->num_rows > 0): ?>
            <div class="text-center mt-4">
                <a href="events.php" class="btn btn-outline-primary btn-lg">View All Events <i class="fas fa-arrow-right ms-2"></i></a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Call to Action Section -->
<?php if (!$auth->isLoggedIn()): ?>
<section class="cta-section py-5">
    <div class="container">
        <div class="cta-card text-center p-5" data-aos="zoom-in">
            <h2 class="mb-3">Become Part of Our Church Family</h2>
            <p class="lead mb-4">Join our community to create events, register for activities, and stay connected.</p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="register.php" class="btn btn-primary btn-lg px-5">Register Now</a>
                <a href="login.php" class="btn btn-outline-light btn-lg px-5">Login</a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Chatbot Widget -->
<style>
.chatbot-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
}
.chatbot-button {
    background-color: #2c3e50;
    color: white;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}
.chatbot-button:hover {
    background-color: #3498db;
    transform: scale(1.05);
}
.chatbot-button i {
    font-size: 28px;
}
.chatbot-window {
    position: absolute;
    bottom: 80px;
    right: 0;
    width: 320px;
    height: 450px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.2);
    display: none;
    flex-direction: column;
    overflow: hidden;
    transition: all 0.3s ease;
}
.chatbot-header {
    background: linear-gradient(135deg, #2c3e50, #3498db);
    color: white;
    padding: 15px;
    text-align: center;
    font-weight: bold;
}
.chatbot-messages {
    flex: 1;
    padding: 15px;
    overflow-y: auto;
    background: #f5f5f5;
    font-size: 14px;
}
.chatbot-message {
    margin-bottom: 12px;
    display: flex;
}
.chatbot-message.bot {
    justify-content: flex-start;
}
.chatbot-message.user {
    justify-content: flex-end;
}
.message-bubble {
    max-width: 80%;
    padding: 10px 12px;
    border-radius: 18px;
    line-height: 1.4;
}
.bot .message-bubble {
    background: #e9ecef;
    color: #2c3e50;
    border-bottom-left-radius: 4px;
}
.user .message-bubble {
    background: #3498db;
    color: white;
    border-bottom-right-radius: 4px;
}
.chatbot-input {
    display: flex;
    padding: 10px;
    border-top: 1px solid #ddd;
    background: white;
}
.chatbot-input input {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 25px;
    outline: none;
}
.chatbot-input button {
    background: #3498db;
    border: none;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-left: 8px;
    cursor: pointer;
    transition: background 0.3s;
}
.chatbot-input button:hover {
    background: #2c3e50;
}
@media (max-width: 480px) {
    .chatbot-window {
        width: 280px;
        right: 0;
        bottom: 70px;
    }
}
</style>

<div class="chatbot-container">
    <div class="chatbot-button" id="chatbotToggle">
        <i class="fas fa-comments"></i>
    </div>
    <div class="chatbot-window" id="chatbotWindow">
        <div class="chatbot-header">
            <i class="fas fa-church me-2"></i> Church Assistant
            <button id="chatbotClose" style="float:right; background:none; border:none; color:white; cursor:pointer;">✕</button>
        </div>
        <div class="chatbot-messages" id="chatbotMessages">
            <div class="chatbot-message bot">
                <div class="message-bubble">Hello! I'm your church assistant. Ask me about events, registration, or how to create an event.</div>
            </div>
        </div>
        <div class="chatbot-input">
            <input type="text" id="chatbotInput" placeholder="Type your question..." onkeypress="if(event.key==='Enter') sendMessage()">
            <button onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
</div>

<script>
function sendMessage() {
    const input = document.getElementById('chatbotInput');
    const message = input.value.trim();
    if (message === '') return;
    
    const messagesDiv = document.getElementById('chatbotMessages');
    const userMsgDiv = document.createElement('div');
    userMsgDiv.className = 'chatbot-message user';
    userMsgDiv.innerHTML = `<div class="message-bubble">${escapeHtml(message)}</div>`;
    messagesDiv.appendChild(userMsgDiv);
    input.value = '';
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
    
    setTimeout(() => {
        const response = getBotResponse(message.toLowerCase());
        const botMsgDiv = document.createElement('div');
        botMsgDiv.className = 'chatbot-message bot';
        botMsgDiv.innerHTML = `<div class="message-bubble">${response}</div>`;
        messagesDiv.appendChild(botMsgDiv);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }, 500);
}

function getBotResponse(msg) {
    if (msg.includes('event') || msg.includes('events')) {
        return "You can view all upcoming events on the <a href='events.php' target='_blank'>Events page</a>. To create an event, please register or login first.";
    } else if (msg.includes('register') || msg.includes('sign up')) {
        return "To join our church community, go to the <a href='register.php' target='_blank'>Registration page</a>. It's free!";
    } else if (msg.includes('login') || msg.includes('sign in')) {
        return "Already have an account? <a href='login.php' target='_blank'>Login here</a>.";
    } else if (msg.includes('create event')) {
        return "Only registered members can create events. After logging in, go to your dashboard and click 'Create Event'.";
    } else if (msg.includes('contact') || msg.includes('help')) {
        return "For more help, please email mcc@gmail.com or call +256 752611682.";
    } else if (msg.includes('calendar')) {
        return "Check our interactive <a href='calendar.php' target='_blank'>Church Calendar</a> to see all upcoming events.";
    } else if (msg.includes('video') || msg.includes('upload')) {
        return "Yes, you can upload video files (MP4, WebM) when creating an event. Max size is 100MB.";
    } else {
        return "Thank you for your message. I'm still learning. Please check our <a href='events.php'>Events page</a> or contact the church office directly.";
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

const toggle = document.getElementById('chatbotToggle');
const windowDiv = document.getElementById('chatbotWindow');
const closeBtn = document.getElementById('chatbotClose');

toggle.addEventListener('click', () => {
    windowDiv.style.display = windowDiv.style.display === 'flex' ? 'none' : 'flex';
});
closeBtn.addEventListener('click', () => {
    windowDiv.style.display = 'none';
});
</script>

<!-- AOS Animation Library -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 800,
        once: true
    });
</script>

<!-- Additional CSS for landing page -->
<style>
/* Custom styles for landing page */
:root {
    --primary: #4a6cf7;
    --primary-dark: #3a5ce0;
    --secondary: #764ba2;
    --dark: #1a1a2e;
    --light: #f8f9fa;
}
.hero-section {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    position: relative;
    padding: 80px 0 120px;
    overflow: hidden;
}
.min-vh-75 {
    min-height: 75vh;
}
.text-gradient {
    background: linear-gradient(135deg, #fff, #ffd966);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}
.hero-wave {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    line-height: 0;
}
.stat-card, .feature-card, .event-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}
.stat-card:hover, .feature-card:hover, .event-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}
.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 5px;
}
.stat-label {
    color: #6c757d;
    font-weight: 500;
}
.feature-icon, .stat-icon {
    color: var(--primary);
}
.section-title {
    font-size: 2.2rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 15px;
}
.section-subtitle {
    font-size: 1.1rem;
    color: #6c757d;
}
.event-card {
    position: relative;
    overflow: hidden;
    max-width: 380px;
    margin: 0 auto;
}
.event-date-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    background: white;
    border-radius: 10px;
    text-align: center;
    padding: 5px 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    z-index: 2;
}
.event-date-badge .day {
    font-size: 1.2rem;
    font-weight: 700;
    display: block;
    line-height: 1;
    color: var(--primary);
}
.event-date-badge .month {
    font-size: 0.7rem;
    text-transform: uppercase;
    color: #6c757d;
}
.event-card-img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}
.event-card-body {
    padding: 20px;
}
.event-card-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 10px;
}
.event-meta {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 10px;
}
.event-meta span {
    display: inline-block;
    margin-right: 15px;
}
.event-card-text {
    font-size: 0.9rem;
    color: #555;
    margin-bottom: 15px;
}
.cta-card {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    border-radius: 30px;
    color: white;
}
.empty-state {
    padding: 40px;
    background: #f8f9fa;
    border-radius: 20px;
}
/* Hero image styling */
.hero-img {
    max-width: 100%;
    border-radius: 30px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    transition: transform 0.3s ease;
}
.hero-img:hover {
    transform: scale(1.02);
}
@media (max-width: 768px) {
    .hero-section {
        padding: 60px 0 80px;
    }
    .min-vh-75 {
        min-height: auto;
    }
    .section-title {
        font-size: 1.8rem;
    }
    .hero-img {
        margin-top: 30px;
        border-radius: 20px;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>