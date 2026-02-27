    </main>
    <footer class="site-footer">
      <div class="site-footer-inner">
        <span class="site-footer-left">
          <?php if ($this->options->footerCopyright): ?>
            <?php echo $this->options->footerCopyright; ?>
          <?php else: ?>
            &copy; <?php echo date('Y'); ?> <?php $this->options->title(); ?>
          <?php endif; ?>
        </span>
        <span class="site-footer-right">Powered by Typecho</span>
      </div>
    </footer>
  </div>
  <script>
    (function () {
      var root = document.documentElement;
      var toggle = document.getElementById('theme-toggle');
      if (!toggle) return;

      var syncState = function () {
        var isDark = root.classList.contains('dark');
        toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        toggle.setAttribute('title', isDark ? '切换到日间模式' : '切换到夜间模式');
      };

      syncState();
      toggle.addEventListener('click', function () {
        root.classList.add('theme-animating');
        var nextDark = !root.classList.contains('dark');
        root.classList.toggle('dark', nextDark);
        try {
          localStorage.setItem('printer-theme-mode', nextDark ? 'dark' : 'light');
        } catch (e) {}
        syncState();
        window.setTimeout(function () {
          root.classList.remove('theme-animating');
        }, 170);
      });
    })();
  </script>
  <?php if ($this->options->analyticsCode): ?>
    <?php echo $this->options->analyticsCode; ?>
  <?php endif; ?>
  <?php $this->footer(); ?>
</body>
</html>
