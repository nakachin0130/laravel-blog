    </main>

    <!-- フッター -->
    <footer class="card mt-5" style="margin-top: 3rem !important; border-radius: 0; background: var(--white);">
        <div class="container">
            <div class="grid grid-3">
                <div>
                    <h5>🚀 Laravel App</h5>
                    <p style="color: var(--gray-600); font-size: var(--font-size-sm);">
                        モダンなWebアプリケーション開発のためのプラットフォーム
                    </p>
                </div>
                <div>
                    <h6>🔗 リンク</h6>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: var(--spacing-xs);">
                            <a href="/" style="color: var(--gray-600); font-size: var(--font-size-sm);">🏠 ホーム</a>
                        </li>
                        <li style="margin-bottom: var(--spacing-xs);">
                            <a href="/posts.php" style="color: var(--gray-600); font-size: var(--font-size-sm);">📋 記事一覧</a>
                        </li>
                        <li style="margin-bottom: var(--spacing-xs);">
                            <a href="/users.php" style="color: var(--gray-600); font-size: var(--font-size-sm);">👥 ユーザー管理</a>
                        </li>
                    </ul>
                </div>
                <div>
                    <h6>📞 サポート</h6>
                    <p style="color: var(--gray-600); font-size: var(--font-size-sm);">
                        お困りの際はお気軽にお問い合わせください
                    </p>
                    <div class="d-flex align-center" style="gap: var(--spacing-sm);">
                        <span style="font-size: 1.5rem;">📧</span>
                        <span style="color: var(--gray-600); font-size: var(--font-size-sm);">support@example.com</span>
                    </div>
                </div>
            </div>
            <hr style="margin: var(--spacing-lg) 0; border: none; border-top: 1px solid var(--gray-200);">
            <div class="d-flex justify-between align-center">
                <p style="color: var(--gray-600); font-size: var(--font-size-sm); margin: 0;">
                    © 2024 Laravel App. All rights reserved.
                </p>
                <div class="d-flex align-center" style="gap: var(--spacing-sm);">
                    <span style="color: var(--gray-600); font-size: var(--font-size-sm);">Made with ❤️</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // スムーススクロール
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // アラートの自動非表示
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease-out';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        // カードのホバーエフェクト
        document.querySelectorAll('.card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-4px)';
                this.style.boxShadow = 'var(--shadow-xl)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'var(--shadow-md)';
            });
        });

        // フォームのバリデーション
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const requiredFields = this.querySelectorAll('[required]');
                let isValid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.style.borderColor = 'var(--danger-color)';
                        field.style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.1)';
                    } else {
                        field.style.borderColor = 'var(--gray-300)';
                        field.style.boxShadow = 'none';
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('必須項目を入力してください。');
                }
            });
        });
    </script>
</body>
</html>
