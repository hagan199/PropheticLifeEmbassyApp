// Shared types and utility functions for Settings components

export interface Category {
  id: number;
  name: string;
  description?: string;
}

export interface Role {
  id: number;
  name: string;
  is_system?: boolean;
}

export interface Permission {
  id: number;
  name: string;
  display_name?: string;
  module?: string;
}

export interface User {
  id: number;
  name: string;
  email?: string;
  phone?: string;
  roles?: Role[];
  permissions?: string[];
}

export function useSettingsUtils() {
  function roleIcon(name: string): string {
    const map: Record<string, string> = {
      Administrator: 'bi bi-person-badge',
      Pastor: 'bi bi-book',
      Usher: 'bi bi-people',
      'Finance Officer': 'bi bi-cash-coin',
      'PR/Follow-up': 'bi bi-megaphone',
      'Department Leader': 'bi bi-diagram-3',
    };
    return map[name] || 'bi bi-person';
  }

  function getRoleColor(name: string): string {
    const colorMap: Record<string, string> = {
      Administrator: 'role-admin',
      Pastor: 'role-pastor',
      Usher: 'role-usher',
      'Finance Officer': 'role-finance',
      'PR/Follow-up': 'role-pr',
      'Department Leader': 'role-dept',
    };
    return colorMap[name] || 'role-default';
  }

  function getRandomColor(str: string): string {
    const colors = ['bg-primary', 'bg-success', 'bg-warning', 'bg-info', 'bg-danger'];
    if (!str) return colors[0];
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
      hash = str.charCodeAt(i) + ((hash << 5) - hash);
    }
    return colors[Math.abs(hash) % colors.length];
  }

  function getPermissionIcon(perm: Permission | string): string {
    let permName = perm;
    if (typeof perm === 'object' && perm !== null) {
      permName = perm.name || '';
    }
    const [category] = String(permName).split('.');
    const iconMap: Record<string, string> = {
      users: 'bi bi-people-fill',
      attendance: 'bi bi-calendar-check-fill',
      finance: 'bi bi-cash-stack',
      contributions: 'bi bi-wallet2',
      expenses: 'bi bi-receipt',
      visitors: 'bi bi-person-plus-fill',
      followups: 'bi bi-telephone-fill',
      broadcasts: 'bi bi-megaphone-fill',
      department: 'bi bi-diagram-3-fill',
      audit: 'bi bi-file-earmark-text-fill',
      reports: 'bi bi-graph-up',
    };
    return iconMap[category] || 'bi bi-check-circle-fill';
  }

  function getRoleDescription(roleName: string): string {
    const descriptions: Record<string, string> = {
      admin: 'Full system access and management',
      Administrator: 'Full system access and management',
      pastor: 'Ministry oversight and leadership',
      Pastor: 'Ministry oversight and leadership',
      usher: 'Attendance recording and guest services',
      Usher: 'Attendance recording and guest services',
      finance: 'Financial management and reporting',
      'Finance Officer': 'Financial management and reporting',
      pr_follow_up: 'Visitor tracking and follow-ups',
      'PR/Follow-up': 'Visitor tracking and follow-ups',
      department_leader: 'Department coordination',
      'Department Leader': 'Department coordination',
    };
    return descriptions[roleName] || 'Standard access';
  }

  function labelFor(p: Permission | string): string {
    if (typeof p === 'object' && p !== null && p.display_name) {
      return p.display_name;
    }
    const permString = typeof p === 'string' ? p : (p?.name || '');
    return permString
      .split('.')
      .map((w: string) => w.charAt(0).toUpperCase() + w.slice(1))
      .join(': ');
  }

  return { roleIcon, getRoleColor, getRandomColor, getPermissionIcon, getRoleDescription, labelFor };
}
