<footer class="footer text-white pt-5 pb-3">
     <style>
        .footer a.text-white:hover,
        .footer a.text-white:focus {
            color:rgb(246, 92, 107) !important; /* Bootstrap primary color */
            text-decoration: underline;
        }
        .footer {
            background-color:rgb(0, 0, 0); /* Dark background */
        }
        .footer h5 {
            color:rgb(246, 92, 107); /* White text for headings */
        }
    </style>
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <img src="./assets/logo2.jpg" alt="" style="width: 150px; height: auto;border-radius:20px" class="mb-3">
                <p class="mb-0">GetJerseys is your ultimate destination for authentic football jerseys, offering a wide range of kits from national teams and clubs around the globe.</p>
                
           
            </div>
            <div class="col-md-2 mb-4">
                <h5 class="mb-3">Quick Links</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="index.php" class="text-white text-decoration-none">Home</a></li>
                    <li class="mb-2"><a href="all-jerseys.php" class="text-white text-decoration-none">All Jerseys</a></li>
                    <li class="mb-2"><a href="categories.php?type=national" class="text-white text-decoration-none">National Teams</a></li>
                    <li class="mb-2"><a href="categories.php?type=club" class="text-white text-decoration-none">Club Teams</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h5 class="mb-3">Categories</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="jerseys.php?type=home" class="text-white text-decoration-none">Home Jerseys</a></li>
                    <li class="mb-2"><a href="jerseys.php?type=away" class="text-white text-decoration-none">Away Jerseys</a></li>
                    <li class="mb-2"><a href="jerseys.php?type=third" class="text-white text-decoration-none">Third Kits</a></li>
                    <li class="mb-2"><a href="jerseys.php?type=goalkeeper" class="text-white text-decoration-none">Goalkeeper Kits</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h5 class="mb-3">Customer Service</h5>
                <ul class="list-unstyled">
                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
                                        <li class="mb-2">
                                            <a href="https://wa.me/447341157876" class="text-white text-decoration-none" target="_blank" rel="noopener">
                                                <i class="bi bi-whatsapp" aria-hidden="true"></i> Contact Us
                                            </a>
                                        </li>
                                        <li class="mb-2">
                                            <a href="mailto:support@getjerseys.uk" class="text-white text-decoration-none">
                                                <i class="bi bi-envelope" aria-hidden="true"></i> support@getjerseys.uk
                                            </a>
                                        </li>
                </ul>
            </div>
        </div>
        <hr class="my-4">
        <div class="row">
            <div class="col-md-6 mb-3 mb-md-0">
                <p class="mb-0">&copy; <?= date('Y') ?> GetJerseys. All Rights Reserved.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <ul class="list-inline mb-0">
                    <li class="list-inline-item"><a href="privacy-policy.php" class="text-white text-decoration-none">Privacy Policy</a></li>
                    <li class="list-inline-item ms-3"><a href="terms-of-service.php" class="text-white text-decoration-none">Terms of Service</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>