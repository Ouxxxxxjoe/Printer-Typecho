    </main>
    <!-- Printer Theme Footer -->
    <footer class="site-footer">
      <div class="site-footer-card">
        <!-- 社交链接 -->
        <div class="site-footer-social">
          <?php if ($this->options->socialGithub): ?>
            <a href="<?php echo htmlspecialchars($this->options->socialGithub, ENT_QUOTES, 'UTF-8'); ?>" class="social-link" target="_blank" rel="noopener" title="GitHub">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
            </a>
          <?php endif; ?>
          <?php if ($this->options->socialTwitter): ?>
            <a href="<?php echo htmlspecialchars($this->options->socialTwitter, ENT_QUOTES, 'UTF-8'); ?>" class="social-link" target="_blank" rel="noopener" title="Twitter">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            </a>
          <?php endif; ?>
          <?php if ($this->options->socialWeibo): ?>
            <a href="<?php echo htmlspecialchars($this->options->socialWeibo, ENT_QUOTES, 'UTF-8'); ?>" class="social-link" target="_blank" rel="noopener" title="微博">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M10.098 20.323c-3.977.391-7.414-1.406-7.672-4.02-.259-2.609 2.759-5.047 6.74-5.441 3.979-.394 7.413 1.404 7.671 4.018.259 2.6-2.759 5.049-6.737 5.439l-.002.004zM9.05 17.219c-.384.616-1.208.884-1.829.602-.612-.279-.793-.991-.406-1.593.379-.595 1.176-.861 1.793-.601.622.263.82.972.442 1.592zm1.27-1.627c-.141.237-.449.353-.689.253-.236-.09-.313-.361-.177-.586.138-.227.436-.346.672-.24.239.09.315.36.18.573h.014zm.176-2.719c-1.893-.493-4.033.45-4.857 2.118-.836 1.704-.026 3.591 1.886 4.21 1.983.64 4.318-.341 5.132-2.179.8-1.793-.201-3.642-2.161-4.149zm7.563-1.224c-.346-.105-.579-.18-.402-.649.386-1.017.425-1.893.003-2.521-.789-1.168-2.947-1.108-5.388-.031 0 0-.772.338-.575-.274.381-1.205.324-2.213-.27-2.8-1.346-1.327-4.928.047-8.001 3.07C1.353 10.476 0 12.555 0 14.359c0 3.457 4.439 5.56 8.783 5.56 5.691 0 9.479-3.302 9.479-5.929 0-1.587-1.339-2.486-2.203-2.741zm.9-5.673c-.063-.146-.228-.213-.368-.147-.142.065-.21.22-.147.366.235.55.356 1.134.356 1.738 0 .38-.05.756-.149 1.12-.043.152.045.31.197.353.152.043.31-.045.353-.197.115-.418.174-.851.174-1.287 0-.68-.14-1.343-.416-1.946zm2.082-1.303c-.126-.293-.457-.428-.742-.301-.285.127-.417.456-.291.749.388.9.586 1.854.586 2.837 0 .618-.081 1.23-.241 1.82-.074.261.078.532.339.606.261.074.532-.078.606-.339.185-.687.28-1.399.28-2.121 0-1.112-.227-2.189-.671-3.211l-.866-.04z"/></svg>
            </a>
          <?php endif; ?>
          <?php if ($this->options->socialEmail): ?>
            <a href="mailto:<?php echo htmlspecialchars($this->options->socialEmail, ENT_QUOTES, 'UTF-8'); ?>" class="social-link" title="邮箱">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
            </a>
          <?php endif; ?>
          <?php if ($this->options->socialRss): ?>
            <a href="<?php echo htmlspecialchars($this->options->socialRss, ENT_QUOTES, 'UTF-8'); ?>" class="social-link" target="_blank" rel="noopener" title="RSS 订阅">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.503 20.752c0 1.794-1.456 3.248-3.251 3.248-1.796 0-3.252-1.454-3.252-3.248 0-1.794 1.456-3.248 3.252-3.248 1.795.001 3.251 1.454 3.251 3.248zm-6.503-12.572v4.811c6.05.062 10.96 4.966 11.022 11.009h4.817c-.062-8.71-7.118-15.758-15.839-15.82zm0-3.368c10.58.046 19.152 8.594 19.183 19.188h4.817c-.03-13.231-10.736-23.982-23.999-24.004v4.816h-.001z"/></svg>
            </a>
          <?php endif; ?>
        </div>

        <!-- 分隔装饰 -->
        <div class="site-footer-divider">
          <span class="divider-line"></span>
          <span class="divider-dot"></span>
          <span class="divider-line"></span>
        </div>

        <!-- 版权信息 -->
        <div class="site-footer-copyright">
          <?php if ($this->options->footerCopyright): ?>
            <?php echo htmlspecialchars($this->options->footerCopyright, ENT_QUOTES, 'UTF-8'); ?>
          <?php else: ?>
            &copy; <?php echo date('Y'); ?> <?php $this->options->title(); ?>
          <?php endif; ?>
        </div>

        <!-- 访问统计 -->
        <div class="site-footer-stats" id="visit-stats">
          <span class="stats-loading">正在统计访问数据...</span>
        </div>

        <!-- 技术标识 -->
        <div class="site-footer-powered">
          <span>Powered by</span>
          <a href="http://typecho.org/" target="_blank" rel="noopener">Typecho</a>
        </div>
      </div>

      <!-- 返回顶部按钮 -->
      <button type="button" class="back-to-top" id="back-to-top" aria-label="返回顶部">
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M7.41 15.41L12 10.83l4.59 4.58L18 14l-6-6-6 6z"/>
        </svg>
      </button>
    </footer>

    <?php
      // 访问统计脚本
      $visitStatsScript = '';
      if (function_exists('printerPaperGetFinalVisitStats') && function_exists('printerPaperFormatVisitCount')) {
        try {
          $stats = printerPaperGetFinalVisitStats();
          $totalVisits = isset($stats['total']) ? (int) $stats['total'] : 0;
          $todayVisits = isset($stats['today']) ? (int) $stats['today'] : 0;
          $visitStatsScript = '<svg viewBox="0 0 24 24" style="width: 14px; height: 14px; vertical-align: middle; margin-right: 4px; fill: currentColor;" focusable="false"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>总访问 ' . printerPaperFormatVisitCount($totalVisits) . ' · 今日 ' . printerPaperFormatVisitCount($todayVisits);
        } catch (Exception $e) {
          $visitStatsScript = '<svg viewBox="0 0 24 24" style="width: 14px; height: 14px; vertical-align: middle; margin-right: 4px; fill: currentColor;" focusable="false"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>总访问 0 · 今日 0';
        }
      } else {
        $visitStatsScript = '<svg viewBox="0 0 24 24" style="width: 14px; height: 14px; vertical-align: middle; margin-right: 4px; fill: currentColor;" focusable="false"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>总访问 0 · 今日 0';
      }
    ?>
    <script>
      (function() {
        var statsEl = document.getElementById('visit-stats');
        if (statsEl) {
          statsEl.innerHTML = '<?php echo $visitStatsScript; ?>';
        }
      })();
    </script>
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
          // 确保在DOM更新后聚焦
          requestAnimationFrame(() => {
            input.focus();
          });
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

    // 返回顶部按钮
    (function () {
      var backToTop = document.getElementById('back-to-top');
      if (!backToTop) return;

      // 显示/隐藏按钮
      var toggleVisibility = function () {
        if (window.scrollY > 300) {
          backToTop.classList.add('visible');
        } else {
          backToTop.classList.remove('visible');
        }
      };

      window.addEventListener('scroll', toggleVisibility, { passive: true });
      toggleVisibility();

      // 点击返回顶部
      backToTop.addEventListener('click', function () {
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      });
    })();
  </script>
  <?php $this->footer(); ?>
</body>
</html>
