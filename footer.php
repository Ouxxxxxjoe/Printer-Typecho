    </main>
    <!-- Printer Theme Visit Stats (Dynamic Content - Do Not Cache) -->
    <footer class="site-footer">
      <div class="site-footer-inner">
        <span class="site-footer-left">
          <?php if ($this->options->footerCopyright): ?>
            <?php echo htmlspecialchars($this->options->footerCopyright, ENT_QUOTES, 'UTF-8'); ?>
          <?php else: ?>
            &copy; <?php echo date('Y'); ?> <?php $this->options->title(); ?>
          <?php endif; ?>
        </span>
        <span class="site-footer-center">
          <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 6px; fill: currentColor;" focusable="false">
            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
          </svg>
          <?php
            // 访问统计（如出错请检查 functions.php 中的函数定义）
            if (function_exists('printerPaperGetFinalVisitStats') && function_exists('printerPaperFormatVisitCount')) {
              try {
                $stats = printerPaperGetFinalVisitStats();
                $totalVisits = isset($stats['total']) ? (int) $stats['total'] : 0;
                $todayVisits = isset($stats['today']) ? (int) $stats['today'] : 0;
                echo 'Total Visits: ' . printerPaperFormatVisitCount($totalVisits) . ' / Today Visits: ' . printerPaperFormatVisitCount($todayVisits);
              } catch (Exception $e) {
                echo 'Total Visits: 0 / Today Visits: 0';
              }
            } else {
              echo 'Total Visits: 0 / Today Visits: 0';
            }
          ?>
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
        var title = isDark ? toggle.getAttribute('data-title-light') : toggle.getAttribute('data-title-dark');
        toggle.setAttribute('title', title || (isDark ? '切换到日间模式' : '切换到夜间模式'));
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
        // 等待过渡动画完成（250ms CSS 过渡 + 50ms 缓冲）
        window.setTimeout(function () {
          root.classList.remove('theme-animating');
        }, 300);
      });
    })();
  </script>
  <?php if ($this->options->analyticsCode): ?>
    <?php echo $this->options->analyticsCode; ?>
  <?php endif; ?>
  <script>
    // 阅读进度条
    (function () {
      var bar = document.getElementById('reading-progress');
      if (!bar) return;
      var article = document.querySelector('article');
      if (!article) return;
      var update = function () {
        var total = article.offsetTop + article.offsetHeight - window.innerHeight;
        var progress = total > 0 ? Math.min(100, Math.max(0, (window.scrollY / total) * 100)) : 100;
        bar.style.width = progress + '%';
        bar.setAttribute('aria-valuenow', Math.round(progress));
      };
      window.addEventListener('scroll', update, { passive: true });
      update();
    })();

    // 搜索框展开/收起
    (function () {
      var form  = document.querySelector('.header-search');
      var input = document.getElementById('header-search-input');
      var btn   = document.querySelector('.header-search-btn');
      if (!form || !input || !btn) return;

      btn.addEventListener('click', function (e) {
        if (!form.classList.contains('open')) {
          e.preventDefault();
          form.classList.add('open');
          input.focus();
        }
        // 已展开时点击提交按钮，表单正常提交
      });

      // 点击表单外部时收起
      document.addEventListener('click', function (e) {
        if (!form.contains(e.target)) {
          form.classList.remove('open');
        }
      });

      // Esc 键收起
      input.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
          form.classList.remove('open');
          btn.focus();
        }
      });
    })();

    // 文章估读时长
    (function () {
      var timeEl = document.getElementById('post-reading-time');
      if (!timeEl) return;
      var content = document.querySelector('article .post-excerpt');
      if (!content) return;
      var chars = content.innerText.replace(/\s+/g, '').length;
      var minutes = Math.max(1, Math.ceil(chars / 200));
      timeEl.textContent = '约 ' + minutes + ' 分钟读完';
    })();
  </script>
  <?php $this->footer(); ?>
</body>
</html>
