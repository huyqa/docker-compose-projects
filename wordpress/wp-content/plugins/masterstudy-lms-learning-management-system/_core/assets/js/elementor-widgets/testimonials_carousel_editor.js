"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _getPrototypeOf(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

var StmLmsProTestimonials = /*#__PURE__*/function (_elementorModules$fro) {
  _inherits(StmLmsProTestimonials, _elementorModules$fro);

  var _super = _createSuper(StmLmsProTestimonials);

  function StmLmsProTestimonials() {
    _classCallCheck(this, StmLmsProTestimonials);

    return _super.apply(this, arguments);
  }

  _createClass(StmLmsProTestimonials, [{
    key: "getDefaultSettings",
    value: function getDefaultSettings() {
      return {
        selectors: {
          carousel: '.stm-testimonials-carousel-wrapper'
        }
      };
    }
  }, {
    key: "getDefaultElements",
    value: function getDefaultElements() {
      var selectors = this.getSettings('selectors');
      var elementSettings = this.getElementSettings();
      return {
        $sliderContainer: this.$element.find(selectors.carousel),
        $sliderData: {
          'autoplay': elementSettings['autoplay'],
          'loop': elementSettings['loop']
        }
      };
    }
  }, {
    key: "bindEvents",
    value: function bindEvents() {
      jQuery(document).ready(this.sliderInit.bind(this));
    }
  }, {
    key: "sliderInit",
    value: function sliderInit() {
      var _this = this,
          autoplayData = false,
          widgetID = _this.elements.$sliderContainer.closest('.elementor-widget-stm_lms_pro_testimonials').data('id'),
          sliderContainer = document.querySelector("[data-id=\"".concat(widgetID, "\"] .stm-testimonials-carousel-wrapper")),
          bullets = document.querySelector("[data-id=\"".concat(widgetID, "\"] .ms-lms-elementor-testimonials-swiper-pagination")),
          sliderWrapper = document.querySelector("[data-id=\"".concat(widgetID, "\"] .elementor-testimonials-carousel"));

      if (_this.elements.$sliderData['autoplay']) {
        autoplayData = {
          delay: 2000
        };
      }

      if (_this.elements.$sliderContainer.length !== 0) {
        var mySwiper = new Swiper(sliderContainer, {
          slidesPerView: 1,
          allowTouchMove: true,
          loop: _this.elements.$sliderData['loop'],
          autoplay: autoplayData,
          pagination: {
            el: bullets,
            clickable: true,
            renderBullet: function renderBullet(index, className) {
              var userThumbnail = '',
                  testimonialItem = sliderWrapper.children[index];

              if (testimonialItem !== null && _typeof(testimonialItem) === 'object') {
                userThumbnail = testimonialItem.getAttribute('data-thumbnail');
              }

              var span = jQuery('<span></span>');
              span.addClass(className);
              span.css("background-image", "url(" + userThumbnail + ")");
              return span.prop('outerHTML');
            }
          }
        });
      }
    }
  }]);

  return StmLmsProTestimonials;
}(elementorModules.frontend.handlers.Base);

jQuery(window).on('elementor/frontend/init', function () {
  var addHandler = function addHandler($element) {
    elementorFrontend.elementsHandler.addHandler(StmLmsProTestimonials, {
      $element: $element
    });
  };

  elementorFrontend.hooks.addAction('frontend/element_ready/stm_lms_pro_testimonials.default', addHandler);
});