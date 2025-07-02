document.addEventListener('DOMContentLoaded', function() {
    const menuButton = document.querySelector('.header__menu-button');
    const mobileMenu = document.querySelector('.header__mobile-menu');

    if (menuButton && mobileMenu) {
        // メニューの開閉状態を管理する関数
        function toggleMenu(isOpen) {
            menuButton.classList.toggle('is-open', isOpen);
            mobileMenu.classList.toggle('is-open', isOpen);
        }

        // メニューボタンクリック時の処理
        menuButton.addEventListener('click', function(e) {
            e.stopPropagation(); // イベントの伝播を停止
            const isCurrentlyOpen = mobileMenu.classList.contains('is-open');
            toggleMenu(!isCurrentlyOpen);
        });

        // モバイルメニュー内のクリックイベントが親要素に伝播するのを防ぐ
        mobileMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // ドキュメント全体のクリックイベント
        document.addEventListener('click', function() {
            if (mobileMenu.classList.contains('is-open')) {
                toggleMenu(false);
            }
        });

        // スクロール時にメニューを閉じる
        window.addEventListener('scroll', function() {
            if (mobileMenu.classList.contains('is-open')) {
                toggleMenu(false);
            }
        });
    }
});