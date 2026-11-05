/**
 * Header / footer OCOP — JavaScript thuần thay cho state React của bản Next.js:
 * sticky header ẩn/hiện khi cuộn, mega menu sản phẩm, popup tìm kiếm (Ctrl/Cmd + K),
 * menu mobile, chat Tawk.to và sự kiện GA4.
 */
(function () {
  'use strict';

  var config = window.OCOP_HEADER || {};
  var header = document.getElementById('ocop-header');
  if (!header) return;

  var state = {
    scrolled: false,
    hidden: false,
    menuOpen: false,
    searchOpen: false,
    productsOpen: false,
    activeCategory: '',
    keyword: '',
    products: null
  };
  var lastScrollY = 0;
  var productsTimer = null;
  var productsRequest = null;

  // ---------- Tiện ích ----------
  function escapeHtml(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  // So khớp không phân biệt hoa thường và dấu tiếng Việt
  function normalize(value) {
    return String(value || '')
      .toLowerCase()
      .normalize('NFD')
      .replace(/[̀-ͯ]/g, '')
      .replace(/đ/g, 'd')
      .replace(/\s+/g, ' ')
      .trim();
  }

  function matchSearchQuery(text, query) {
    var q = normalize(query);
    if (!q) return false;
    var t = normalize(text);
    return q.split(' ').every(function (word) {
      return t.indexOf(word) !== -1;
    });
  }

  function searchUrl(keyword) {
    return config.baseUrl + (keyword ? '?search=' + encodeURIComponent(keyword) : '');
  }

  function trackEvent(name, params) {
    if (typeof window.gtag === 'function') {
      window.gtag('event', name, params || {});
    }
  }

  // Tải danh sách sản phẩm một lần, dùng chung cho mega menu và tìm kiếm
  function loadProducts() {
    if (productsRequest) return productsRequest;
    productsRequest = fetch(config.productsApi, { credentials: 'same-origin' })
      .then(function (res) {
        return res.ok ? res.json() : [];
      })
      .catch(function () {
        return [];
      })
      .then(function (data) {
        state.products = Array.isArray(data) ? data : [];
        renderMegaProducts();
        renderSearch();
        return state.products;
      });
    return productsRequest;
  }

  // ---------- Header: nền, ẩn/hiện khi cuộn ----------
  function renderHeader() {
    header.classList.toggle('is-solid', state.scrolled || state.menuOpen);
    header.classList.toggle('is-hidden', state.hidden && !state.menuOpen && !state.searchOpen);
    header.classList.toggle('is-menu-open', state.menuOpen);
  }

  function handleScroll() {
    var currentY = window.pageYOffset || document.documentElement.scrollTop;
    state.scrolled = currentY > 24;
    if (currentY > 140 && currentY > lastScrollY + 8) {
      state.hidden = true;
      setProductsOpen(false);
    } else if (currentY < lastScrollY - 6 || currentY <= 80) {
      state.hidden = false;
    }
    lastScrollY = currentY;
    renderHeader();
  }

  // ---------- Mega menu sản phẩm ----------
  var productsItem = document.getElementById('ocop-nav-products');
  var mega = document.getElementById('ocop-mega');
  var megaProducts = document.getElementById('ocop-mega-products');
  var megaActiveName = document.getElementById('ocop-mega-active-name');
  var megaActiveLink = document.getElementById('ocop-mega-active-link');
  var megaCats = mega ? mega.querySelectorAll('.ocop-mega-cat') : [];

  if (megaCats.length > 0) {
    state.activeCategory = megaCats[0].getAttribute('data-code');
  }

  function setProductsOpen(open) {
    if (!mega || state.productsOpen === open) return;
    state.productsOpen = open;
    mega.hidden = !open;
    productsItem.classList.toggle('is-open', open);
    if (open) loadProducts();
  }

  function handleProductsEnter() {
    if (productsTimer) {
      clearTimeout(productsTimer);
      productsTimer = null;
    }
    setProductsOpen(true);
  }

  function handleProductsLeave() {
    productsTimer = setTimeout(function () {
      setProductsOpen(false);
    }, 220);
  }

  function setActiveCategory(link) {
    state.activeCategory = link.getAttribute('data-code');
    Array.prototype.forEach.call(megaCats, function (item) {
      item.classList.toggle('is-selected', item === link);
    });
    megaActiveName.textContent = link.getAttribute('data-name');
    megaActiveLink.setAttribute('href', link.getAttribute('href'));
    renderMegaProducts();
  }

  function renderMegaProducts() {
    if (!megaProducts) return;
    if (state.products === null) {
      megaProducts.innerHTML = '<p class="ocop-mega-empty">Đang tải sản phẩm...</p>';
      return;
    }
    // Chỉ lấy sản phẩm thuộc đúng danh mục đang chọn, bỏ tên trùng
    var seenNames = {};
    var items = state.products
      .filter(function (p) {
        var key = p.name.toLowerCase();
        if (p.categoryCode !== state.activeCategory || seenNames[key]) return false;
        seenNames[key] = true;
        return true;
      })
      .slice(0, 8);

    if (items.length === 0) {
      megaProducts.innerHTML =
        '<p class="ocop-mega-empty">Đang cập nhật danh mục cho ' + escapeHtml(megaActiveName.textContent) + '.</p>';
      return;
    }
    megaProducts.innerHTML =
      '<div class="ocop-mega-products">' +
      items
        .map(function (p) {
          var out = p.inStock === false;
          return (
            '<a class="ocop-mega-product" href="' + escapeHtml(p.url) + '">' +
            '<div class="ocop-mega-product-main">' +
            '<span class="ocop-stock-dot' + (out ? ' is-out' : '') + '"></span>' +
            '<span class="ocop-mega-product-name">' + escapeHtml(p.name) + '</span>' +
            (out ? '<span class="ocop-out-badge">Hết hàng</span>' : '') +
            '</div>' +
            config.icons.chevronRight +
            '</a>'
          );
        })
        .join('') +
      '</div>';
  }

  if (productsItem && mega) {
    productsItem.addEventListener('mouseenter', handleProductsEnter);
    productsItem.addEventListener('mouseleave', handleProductsLeave);
    mega.addEventListener('mouseenter', handleProductsEnter);
    mega.addEventListener('mouseleave', handleProductsLeave);
    Array.prototype.forEach.call(megaCats, function (link) {
      link.addEventListener('mouseenter', function () {
        setActiveCategory(link);
      });
    });
    // Bàn phím: mở mega menu khi focus vào mục Sản phẩm
    productsItem.addEventListener('focusin', handleProductsEnter);
    mega.addEventListener('focusin', handleProductsEnter);
    mega.addEventListener('focusout', function (e) {
      if (!mega.contains(e.relatedTarget) && !productsItem.contains(e.relatedTarget)) {
        handleProductsLeave();
      }
    });
  }

  // ---------- Popup tìm kiếm ----------
  var search = document.getElementById('ocop-search');
  var searchForm = document.getElementById('ocop-search-form');
  var searchInput = document.getElementById('ocop-search-input');
  var searchTags = document.getElementById('ocop-search-tags');
  var searchList = document.getElementById('ocop-search-list');
  var searchLabel = document.getElementById('ocop-search-results-label');

  // "Tìm nhiều hôm nay": ưu tiên Bán chạy, sau đó mỗi danh mục một sản phẩm, rồi bù sản phẩm còn lại
  function quickSearchTags(products) {
    var tags = [];
    var seenNames = {};
    var seenCategories = {};

    function add(p, markCategory) {
      var label = p.name.trim();
      seenNames[label.toLowerCase()] = true;
      if (markCategory) seenCategories[p.categoryCode] = true;
      tags.push({ label: label, href: searchUrl(label) });
    }

    products.forEach(function (p) {
      if (tags.length >= 6 || p.badge !== 'Bán chạy') return;
      if (!seenNames[p.name.trim().toLowerCase()]) add(p, true);
    });
    products.forEach(function (p) {
      if (tags.length >= 6) return;
      if (!seenNames[p.name.trim().toLowerCase()] && !seenCategories[p.categoryCode]) add(p, true);
    });
    products.forEach(function (p) {
      if (tags.length >= 6) return;
      if (!seenNames[p.name.trim().toLowerCase()]) add(p, false);
    });
    return tags;
  }

  function liveSearchResults(products) {
    var q = state.keyword.trim();
    if (!q) {
      var featured = products.filter(function (p) {
        return p.badge === 'Bán chạy' || p.badge === 'Mới về';
      });
      return (featured.length > 0 ? featured : products).slice(0, 4);
    }
    return products
      .filter(function (p) {
        return (
          matchSearchQuery(p.name, q) ||
          matchSearchQuery(p.categoryName, q) ||
          matchSearchQuery(p.specSummary, q) ||
          matchSearchQuery(p.id, q)
        );
      })
      .slice(0, 5);
  }

  function renderSearch() {
    if (!search || search.hidden) return;
    var keyword = state.keyword.trim();
    searchLabel.textContent = keyword ? 'Kết quả gợi ý nhanh:' : 'Sản phẩm nổi bật:';

    if (state.products === null) {
      searchTags.innerHTML = '';
      searchList.innerHTML = '<p class="ocop-search-empty">Đang tải sản phẩm...</p>';
      return;
    }

    searchTags.innerHTML = quickSearchTags(state.products)
      .map(function (tag) {
        return '<a class="ocop-search-tag" href="' + escapeHtml(tag.href) + '">' + escapeHtml(tag.label) + '</a>';
      })
      .join('');

    var results = liveSearchResults(state.products);
    if (results.length === 0) {
      searchList.innerHTML =
        '<p class="ocop-search-empty">Không tìm thấy sản phẩm khớp với từ khóa &ldquo;' +
        escapeHtml(state.keyword) +
        '&rdquo;. Bấm <strong>Tìm ngay</strong> để gửi yêu cầu báo giá theo mặt hàng riêng.</p>';
      return;
    }
    searchList.innerHTML = results
      .map(function (p) {
        return (
          '<a class="ocop-result" href="' + escapeHtml(p.url) + '">' +
          '<div class="ocop-result-main">' +
          '<img class="ocop-result-img" src="' + escapeHtml(p.image || config.placeholder) + '" alt="' + escapeHtml(p.name) + '" loading="lazy" width="64" height="48">' +
          '<div class="ocop-result-text">' +
          '<span class="ocop-result-cat">' + escapeHtml(p.categoryName) + '</span>' +
          '<h4 class="ocop-result-name">' + escapeHtml(p.name) + '</h4>' +
          '</div>' +
          '</div>' +
          '<span class="ocop-result-more"><span>Chi tiết</span>' + config.icons.arrowRight + '</span>' +
          '</a>'
        );
      })
      .join('');

    // Ảnh lỗi thì dùng ảnh thay thế
    Array.prototype.forEach.call(searchList.querySelectorAll('img'), function (img) {
      img.addEventListener('error', function onError() {
        img.removeEventListener('error', onError);
        img.src = config.placeholder;
      });
    });
  }

  function setSearchOpen(open) {
    if (!search) return;
    state.searchOpen = open;
    search.hidden = !open;
    if (open) {
      setMenuOpen(false);
      setProductsOpen(false);
      state.keyword = searchInput.value;
      renderSearch();
      loadProducts();
      setTimeout(function () {
        searchInput.focus();
      }, 50);
    }
    renderHeader();
  }

  if (search) {
    Array.prototype.forEach.call(document.querySelectorAll('[data-ocop-search-open]'), function (btn) {
      btn.addEventListener('click', function () {
        setSearchOpen(true);
      });
    });
    Array.prototype.forEach.call(search.querySelectorAll('[data-ocop-search-close]'), function (btn) {
      btn.addEventListener('click', function () {
        setSearchOpen(false);
      });
    });
    searchInput.addEventListener('input', function () {
      state.keyword = searchInput.value;
      renderSearch();
    });
    searchForm.addEventListener('submit', function (e) {
      e.preventDefault();
      setSearchOpen(false);
      window.location.href = searchUrl(searchInput.value.trim());
    });
  }

  // ---------- Menu mobile ----------
  var drawer = document.getElementById('ocop-drawer');
  var menuToggle = document.getElementById('ocop-menu-toggle');
  var subToggle = document.getElementById('ocop-drawer-sub-toggle');
  var sub = document.getElementById('ocop-drawer-sub');

  function setMenuOpen(open) {
    if (!drawer || state.menuOpen === open) return;
    state.menuOpen = open;
    drawer.hidden = !open;
    menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    menuToggle.setAttribute('aria-label', open ? 'Đóng menu điều hướng' : 'Mở menu điều hướng');
    renderHeader();
  }

  if (drawer && menuToggle) {
    menuToggle.addEventListener('click', function () {
      setMenuOpen(!state.menuOpen);
    });
    Array.prototype.forEach.call(drawer.querySelectorAll('[data-ocop-drawer-close]'), function (btn) {
      btn.addEventListener('click', function () {
        setMenuOpen(false);
      });
    });
  }
  if (subToggle && sub) {
    subToggle.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      var open = sub.hidden;
      sub.hidden = !open;
      subToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  // ---------- Chat Tawk.to ----------
  function openTawkChat(sourceLocation) {
    if (!config.tawkPropertyId) return;
    trackEvent('open_live_chat', { sourceLocation: sourceLocation });
    if (window.Tawk_API && typeof window.Tawk_API.maximize === 'function') {
      window.Tawk_API.maximize();
      return;
    }
    window.Tawk_API = window.Tawk_API || {};
    window.Tawk_LoadStart = new Date();
    window.Tawk_API.onLoad = function () {
      window.Tawk_API.maximize();
    };
    var script = document.createElement('script');
    script.async = true;
    script.src = 'https://embed.tawk.to/' + config.tawkPropertyId + '/' + (config.tawkWidgetId || 'default');
    script.charset = 'UTF-8';
    script.setAttribute('crossorigin', '*');
    document.body.appendChild(script);
  }

  Array.prototype.forEach.call(document.querySelectorAll('[data-ocop-chat]'), function (btn) {
    btn.addEventListener('click', function () {
      setMenuOpen(false);
      openTawkChat(btn.getAttribute('data-ocop-chat'));
    });
  });

  // ---------- Sự kiện GA4 ----------
  Array.prototype.forEach.call(document.querySelectorAll('[data-ocop-track]'), function (el) {
    el.addEventListener('click', function () {
      trackEvent(el.getAttribute('data-ocop-track'), { sourceLocation: el.getAttribute('data-ocop-source') });
    });
  });

  // ---------- Phím tắt ----------
  window.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      if (state.searchOpen) setSearchOpen(false);
      if (state.productsOpen) setProductsOpen(false);
      if (state.menuOpen) setMenuOpen(false);
    }
    if ((e.metaKey || e.ctrlKey) && e.key && e.key.toLowerCase() === 'k') {
      e.preventDefault();
      setSearchOpen(!state.searchOpen);
    }
  });

  handleScroll();
  window.addEventListener('scroll', handleScroll, { passive: true });
  renderMegaProducts();
})();
