import { ResizeSensor } from 'css-element-queries';
import StickySidebar from 'sticky-sidebar';

const options = JSON.parse(StickyWidgetArea.options);

let sidebar;
let observer;

let sidebarElement;
let containerElement;
let innerWrapperElement;

window.ResizeSensor = ResizeSensor;

const initSticky = () => {
  const {
    selector = `*[data-sticky-widget-area-role='sidebar']`,
    containerSelector = `*[data-sticky-widget-area-role='container']`,
    innerWrapperSelector = `*[data-sticky-widget-area-role='sidebar-inner']`
  } = options;

  sidebarElement = document.querySelector(selector);
  containerElement = document.querySelector(containerSelector);
  innerWrapperElement = document.querySelector(innerWrapperSelector);

  if (sidebarElement) {
    const adminBar = document.querySelector('#wpadminbar');

    let topSpacing = 0;

    if (adminBar) {
      topSpacing+= adminBar.getBoundingClientRect().height;
    }

    topSpacing+= options.topSpacing ? options.topSpacing : 0;

    sidebar = new StickySidebar(sidebarElement, {
      resizeSensor: false,
      topSpacing: 0,
      bottomSpacing: 0,
      containerSelector,
      innerWrapperSelector,
      minWidth: 768,
      ...options,
      topSpacing: topSpacing,
      // resizeSensor: false,
    });

    window.requestAnimationFrame(updateSticky);

    if (!observer) {
      // zu überwachende Zielnode (target) auswählen
      const target = document.body;

      // eine Instanz des Observers erzeugen
      observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          if (mutation.target === sidebarElement || sidebarElement.contains(mutation.target)) {
            // Skip sidebar mutation (for now)
            return;
          }

          updateSticky();
        });
      });

      observer.observe(target, {
        attributes: true,
        childList: true,
        subtree: true,
        characterData: true,
        attributeFilter: [
          'style',
          'class'
        ]
      });
    }
  }
};

const destroySticky = () => {
  observer.disconnect();
  observer = null;

  sidebar.destroy();
  sidebar = null;

  sidebarElement = null;
  containerElement = null;
  innerWrapperElement = null;
};

const updateSticky = () => {
  if (sidebar) {
    // Fix glitch when content is smaller
    const innerHeight = innerWrapperElement.scrollHeight;

    sidebarElement.style.minHeight = `${innerHeight}px`;

    // TODO: Flip roles when content is smaller than sidebar
    /*
    const containerHeight = containerElement.scrollHeight;
    const sidebarHeight = sidebarElement.scrollHeight;
    const offsetHeight = sidebarElement.offsetHeight;
    const innerHeight = innerWrapperElement.scrollHeight;

    containerElement.style.minHeight = `${contentHeight}px`;

    const contentHeight = Math.max(
      ...[ ...containerElement.childNodes ]
        .filter((child) => child.nodeType === 1 && child !== sidebarElement && !child.className.match(/resize-sensor/))
        .map((child) => child.scrollHeight)
    );

    if (innerHeight > contentHeight) {
      console.warn('not defined');
    } else {
      console.log('regular');
    }
    */

    sidebar.updateSticky();
  }
}

const readyHandler = (event) => {
  if (sidebar) {
    destroySticky();
  }

  initSticky();
}

document.addEventListener('turbolinks:load', readyHandler);
document.addEventListener('DOMContentLoaded', readyHandler);
window.addEventListener('resize', updateSticky);
