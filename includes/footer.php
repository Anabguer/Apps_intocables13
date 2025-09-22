    </main>
    
    <!-- Botones de scroll rápido -->
    <div class="scroll-buttons">
        <button id="scroll-to-top" class="scroll-btn scroll-top" title="Subir al inicio">
            <span>↑</span>
        </button>
        <button id="scroll-to-bottom" class="scroll-btn scroll-bottom" title="Ir al final">
            <span>↓</span>
        </button>
    </div>
    
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Intocables - Fotos y Videos. Todos los derechos reservados.</p>
        </div>
    </footer>
    
<?php
require_once __DIR__ . '/paths.php';
?>
    <script src="<?php echo JS_URL; ?>main.js?v=<?php echo filemtime(__DIR__ . '/../js/main.js'); ?>"></script>
</body>
</html>
