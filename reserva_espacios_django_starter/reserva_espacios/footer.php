        </div>
    </main>

    <footer class="footer has-background-light">
        <div class="content has-text-centered">
            <p>
                <strong><?php echo APP_NAME; ?></strong> - Proyecto GP2
            </p>
            <p class="is-size-7 has-text-grey">
                © 2025 - Todos los derechos reservados
            </p>
        </div>
    </footer>

    <script>
        // Navbar burger toggle
        document.addEventListener('DOMContentLoaded', () => {
            const navbarBurgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);
            if (navbarBurgers.length > 0) {
                navbarBurgers.forEach(el => {
                    el.addEventListener('click', () => {
                        const target = el.dataset.target;
                        const $target = document.getElementById(target);
                        el.classList.toggle('is-active');
                        $target.classList.toggle('is-active');
                    });
                });
            }

            // Close notifications
            (document.querySelectorAll('.notification .delete') || []).forEach(($delete) => {
                const $notification = $delete.parentNode;
                $delete.addEventListener('click', () => {
                    $notification.parentNode.removeChild($notification);
                });
            });
        });
    </script>
</body>
</html>

