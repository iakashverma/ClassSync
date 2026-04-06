<!-- Footer -->
<footer class="footer">
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> Class Sync | Classroom Management System</p>
        <p style="margin-top: 5px;">ClassSync | Crafted with ❤️ by Akash Verma</p>
    </div>
</footer>

<script src="/ClassSync/assets/js/main.js"></script>
<?php if (isset($extra_js)): ?>
    <?php foreach ($extra_js as $js): ?>
        <script src="/ClassSync/assets/js/<?php echo $js; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>

</html>