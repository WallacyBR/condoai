import { ref, computed, watch } from 'vue';
import { useRoute } from 'vue-router';

export function useMenuState() {
  const route = useRoute();
  const expandedSubmenus = ref({});
  const hoveredSubmenu = ref(null);
  const hoveredTooltip = ref(null);

  const toggleSubmenu = (path) => {
    expandedSubmenus.value[path] = !expandedSubmenus.value[path];
  };

  const handleSubmenuHover = (path, isHovered) => {
    hoveredSubmenu.value = isHovered ? path : null;
  };

  const handleTooltipHover = (path, isHovered) => {
    hoveredTooltip.value = isHovered ? path : null;
  };

  const isCurrentRoute = (path) => {
    if (path === route.path) return true;
    // Check if the current route is a child of the given path
    if (path.endsWith('/')) {
      return route.path.startsWith(path);
    } else {
      return route.path.startsWith(path + '/');
    }
  };

  const initializeSubmenus = (menuItems) => {
    const currentPath = route.path;
    
    // Para cada item com children, verificar se a rota atual pertence a esse submenu
    const itemsWithChildren = menuItems.filter(item => item.children);
    
    itemsWithChildren.forEach(item => {
      const hasActiveChild = item.children.some(child => isCurrentRoute(child.path));
      if (hasActiveChild || isCurrentRoute(item.path)) {
        expandedSubmenus.value[item.path] = true;
      }
    });
  };

  const isSubmenuActive = (item) => {
    return isCurrentRoute(item.path) || 
           (item.children && item.children.some(child => isCurrentRoute(child.path))) || 
           expandedSubmenus.value[item.path];
  };

  const shouldShowSubmenu = (item, isCollapsed) => {
    return item.children && (
      expandedSubmenus.value[item.path] || 
      (isCollapsed && hoveredSubmenu.value === item.path)
    );
  };

  return {
    expandedSubmenus,
    hoveredSubmenu,
    hoveredTooltip,
    toggleSubmenu,
    handleSubmenuHover,
    handleTooltipHover,
    isCurrentRoute,
    initializeSubmenus,
    isSubmenuActive,
    shouldShowSubmenu
  };
} 