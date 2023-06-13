/**
 * el - the element on which the directive is applied
 * binding - object with information about the directive
 * @type {{unmounted(*): void, mounted(*, *): void}}
 */
export const clickOutside = {
  mounted(el, binding) {
    el.clickOutsideEvent = function (event) {
      // whether the clicked element is the element on which the directive is applied, or whether it is inside this element. If not, then execute the next line.
      if (!(el === event.target || el.contains(event.target))) {
        /**
         * call the function that was passed as a value to the directive (as toggleUserMenu)
         *
         * event - the event object
         * el - the element on which the directive is applied
         */
        binding.value(event, el);
      }
    };
    // when clicking on any page element, the 'clickOutsideEvent' method will be called
    document.body.addEventListener("click", el.clickOutsideEvent);
  },
  // will be called when the element is removed from the page
  unmounted(el) {
    // after removing an element from the page, the 'clickOutsideEvent' method will no longer be called
    document.body.removeEventListener("click", el.clickOutsideEvent);
  },
};
