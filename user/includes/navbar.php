<nav id="nav">
    <ul>
        <li>
            <a href="<?php echo (basename($_SERVER['PHP_SELF']) == 'quote.php') ? 'home#home' : '#home'; ?>">Home</a>
        </li>
        <li>
            <a href="<?php echo (basename($_SERVER['PHP_SELF']) == 'quote.php') ? 'home#services' : '#services'; ?>">Services</a>
        </li>
        <li>
            <a href="<?php echo (basename($_SERVER['PHP_SELF']) == 'quote.php') ? 'home#gallery' : '#gallery'; ?>">Gallery</a>
        </li>
        <li>
            <a href="<?php echo (basename($_SERVER['PHP_SELF']) == 'quote.php') ? 'home#contact' : '#contact'; ?>">Contact</a>
        </li>
        <li>
            <a href="quote.php" <?php if (basename($_SERVER['PHP_SELF']) == 'quote.php') echo 'class="active"'; ?>>Quote</a>
        </li>
        <li class="profile-dropdown">
            <a href="#" class="profile-icon">
                <img src="images/icon.png" alt="" height="24" width="24">
            </a>
            <ul class="dropdown-menu">
                <li><a class="profile" id="profile">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </li>
    </ul>
</nav>

<style>
    .profile-dropdown {
        position: relative;
        display: inline-block;
    }
    
    .profile-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #f0f0f0;
        color: #333;
    }
    
    .profile-icon svg {
        width: 40px;
        height: 40px;
    }
    
    .dropdown-menu {
        display: none;
        position: absolute;
        right: 0;
        background-color: #fff;
        min-width: 160px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 1;
        border-radius: 4px;
        padding: 0;
        margin: 0;
        list-style: none;
    }
    
    .dropdown-menu li a {
        color: #333;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
    }
    
    .dropdown-menu li a:hover {
        background-color: #f1f1f1;
    }
    
    .profile-dropdown:hover .dropdown-menu {
        display: block;
    }
</style>