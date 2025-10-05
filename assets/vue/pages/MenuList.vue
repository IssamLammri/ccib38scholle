<template>
  <div>
    <!-- Bouton toggle (mobile) -->
    <button
        class="sidebar-toggle"
        @click="toggleSidebar"
        aria-label="Ouvrir/fermer le menu"
    >
      <i :class="isSidebarOpen ? 'fas fa-times' : 'fas fa-bars'"></i>
    </button>

    <!-- Overlay (mobile) -->
    <div
        v-if="isSidebarOpen"
        class="sidebar-overlay"
        @click="toggleSidebar"
    ></div>

    <!-- Sidebar -->
    <nav
        class="sidebar modern-sidebar"
        :class="{ open: isSidebarOpen }"
        role="navigation"
        aria-label="Menu principal"
    >
      <div class="sidebar-header mb-4">
        <div class="logo-container">
          <div>
            <img :src="logoSrc" alt="Logo" />
          </div>
        </div>
      </div>

      <ul class="sidebar-menu list-unstyled">
        <!-- Tableau de bord -->
        <template v-if="isAdmin || isManager">
          <li>
            <a
                :href="$routing.generate('app_dashboard')"
                class="menu-link"
                :class="{ active: isActiveRoute('app_dashboard') }"
                :aria-current="isActiveRoute('app_dashboard') ? 'page' : null"
            >
              <i class="fa-solid fa-chart-line menu-icon"></i>
              <span class="menu-text">Tableau de bord</span>
            </a>
          </li>
        </template>

        <template v-else-if="isParent">
          <li>
            <a
                :href="$routing.generate('app_dashboard_parent')"
                class="menu-link"
                :class="{ active: isActiveRoute('app_dashboard_parent') }"
                :aria-current="isActiveRoute('app_dashboard_parent') ? 'page' : null"
            >
              <i class="fa-solid fa-chart-line menu-icon"></i>
              <span class="menu-text">Tableau de bord</span>
            </a>
          </li>
        </template>

        <!-- Administration -->
        <template v-if="isAdmin">
          <li class="menu-header"><span>Administration</span></li>
          <li>
            <a
                :href="$routing.generate('user_index')"
                class="menu-link"
                :class="{ active: isActiveRoute('user_index') }"
                :aria-current="isActiveRoute('user_index') ? 'page' : null"
            >
              <i class="fa-solid fa-users-cog menu-icon"></i>
              <span class="menu-text">Utilisateurs</span>
            </a>
          </li>
        </template>

        <!-- Gestion -->
        <template v-if="isAdmin || isManager">
          <li class="menu-header"><span>Gestion</span></li>

          <!-- Inscriptions -->
          <li class="has-submenu" :class="{ open: open.inscriptions }">
            <button
                class="submenu-toggle menu-link"
                @click="toggle('inscriptions')"
                :aria-expanded="open.inscriptions ? 'true' : 'false'"
                aria-controls="submenu-inscriptions"
            >
              <i class="fa-solid fa-user-plus menu-icon"></i>
              <span class="menu-text">Inscriptions</span>
              <i class="fa-solid fa-chevron-down toggle-icon"></i>
            </button>
            <ul id="submenu-inscriptions" class="submenu list-unstyled">
              <li v-if="isAdmin">
                <a
                    :href="$routing.generate('app_registers_academic_support')"
                    :class="{ active: isActiveRoute('app_registers_academic_support') }"
                    :aria-current="isActiveRoute('app_registers_academic_support') ? 'page' : null"
                >
                  <span class="submenu-dot"></span>
                  Soutien scolaire
                </a>
              </li>
              <li v-if="isManager">
                <a
                    :href="$routing.generate('app_registers')"
                    :class="{ active: isActiveRoute('app_registers') }"
                    :aria-current="isActiveRoute('app_registers') ? 'page' : null"
                >
                  <span class="submenu-dot"></span>
                  Langue arabe
                </a>
              </li>
            </ul>
          </li>

          <!-- Pédagogie -->
          <li class="has-submenu" :class="{ open: open.pedagogie }">
            <button
                class="submenu-toggle menu-link"
                @click="toggle('pedagogie')"
                :aria-expanded="open.pedagogie ? 'true' : 'false'"
                aria-controls="submenu-pedagogie"
            >
              <i class="fa-solid fa-chalkboard-teacher menu-icon"></i>
              <span class="menu-text">Pédagogie</span>
              <i class="fa-solid fa-chevron-down toggle-icon"></i>
            </button>
            <ul id="submenu-pedagogie" class="submenu list-unstyled">
              <li>
                <a
                    :href="$routing.generate('app_teacher_index')"
                    :class="{ active: isActiveRoute('app_teacher_index') }"
                    :aria-current="isActiveRoute('app_teacher_index') ? 'page' : null"
                >
                  <span class="submenu-dot"></span>Enseignants
                </a>
              </li>
              <li>
                <a
                    :href="$routing.generate('app_student_index')"
                    :class="{ active: isActiveRoute('app_student_index') }"
                    :aria-current="isActiveRoute('app_student_index') ? 'page' : null"
                >
                  <span class="submenu-dot"></span>Élèves
                </a>
              </li>
              <li>
                <a
                    :href="$routing.generate('app_parent_entity_index')"
                    :class="{ active: isActiveRoute('app_parent_entity_index') }"
                    :aria-current="isActiveRoute('app_parent_entity_index') ? 'page' : null"
                >
                  <span class="submenu-dot"></span>Parents
                </a>
              </li>
              <li>
                <a
                    :href="$routing.generate('app_study_class_index')"
                    :class="{ active: isActiveRoute('app_study_class_index') }"
                    :aria-current="isActiveRoute('app_study_class_index') ? 'page' : null"
                >
                  <span class="submenu-dot"></span>Classes
                </a>
              </li>
              <li>
                <a
                    :href="$routing.generate('app_room_index')"
                    :class="{ active: isActiveRoute('app_room_index') }"
                    :aria-current="isActiveRoute('app_room_index') ? 'page' : null"
                >
                  <span class="submenu-dot"></span>Salles
                </a>
              </li>
              <li>
                <a
                    :href="$routing.generate('app_book_list')"
                    :class="{ active: isActiveRoute('app_book_list') }"
                    :aria-current="isActiveRoute('app_book_list') ? 'page' : null"
                >
                  <span class="submenu-dot"></span>Livres
                </a>
              </li>
            </ul>
          </li>

          <!-- Finances -->
          <li class="has-submenu" :class="{ open: open.finances }">
            <button
                class="submenu-toggle menu-link"
                @click="toggle('finances')"
                :aria-expanded="open.finances ? 'true' : 'false'"
                aria-controls="submenu-finances"
            >
              <i class="fa-solid fa-euro-sign menu-icon"></i>
              <span class="menu-text">Finances</span>
              <i class="fa-solid fa-chevron-down toggle-icon"></i>
            </button>
            <ul id="submenu-finances" class="submenu list-unstyled">
              <li>
                <a
                    :href="$routing.generate('payments_list')"
                    :class="{ active: isActiveRoute('payments_list') }"
                    :aria-current="isActiveRoute('payments_list') ? 'page' : null"
                >
                  <span class="submenu-dot"></span>Paiements
                </a>
              </li>
              <li>
                <a
                    :href="$routing.generate('app_invoice_index')"
                    :class="{ active: isActiveRoute('app_invoice_index') }"
                    :aria-current="isActiveRoute('app_invoice_index') ? 'page' : null"
                >
                  <span class="submenu-dot"></span>Factures
                </a>
              </li>
            </ul>
          </li>
        </template>

        <!-- Espace Enseignant -->
        <template v-if="isTeacher">
          <li class="menu-header"><span>Mon Espace</span></li>
          <li>
            <a
                :href="$routing.generate('app_session_index')"
                class="menu-link"
                :class="{ active: isActiveRoute('app_session_index') }"
                :aria-current="isActiveRoute('app_session_index') ? 'page' : null"
            >
              <i class="fas fa-calendar-alt menu-icon"></i>
              <span class="menu-text">Mes Sessions</span>
            </a>
          </li>
        </template>

        <!-- Espace Parent -->
        <template v-if="isParent">
          <li class="menu-header"><span>Mon Espace Parent</span></li>
          <li>
            <a
                :href="$routing.generate('app_dashboard_parent')"
                class="menu-link"
                :class="{ active: isActiveRoute('app_dashboard_parent') }"
                :aria-current="isActiveRoute('app_dashboard_parent') ? 'page' : null"
            >
              <i class="fas fa-home menu-icon"></i>
              <span class="menu-text">Tableau de bord</span>
            </a>
          </li>
        </template>
      </ul>
    </nav>
  </div>
</template>

<script>
export default {
  name: "ModernSidebar",
  props: {
    currentUser: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      logoSrc: "/static/icons/logoccib38.webp",
      isSidebarOpen: false,
      open: {
        inscriptions: false,
        pedagogie: false,
        finances: false
      }
    };
  },
  computed: {
    roles() {
      return Array.isArray(this.currentUser?.roles) ? this.currentUser.roles : [];
    },
    isAdmin()   {
      return this.roles.includes('ROLE_ADMIN');
      },
    isManager() {
      return this.roles.includes('ROLE_MANAGER') || this.roles.includes('ROLE_ADMIN');
      },
    isTeacher() {
      return this.roles.includes('ROLE_TEACHER') || this.roles.includes('ROLE_ADMIN') || this.roles.includes('ROLE_MANAGER');
      },
    isParent()  {
      return this.roles.includes('ROLE_PARENT') || this.roles.includes('ROLE_ADMIN') || this.roles.includes('ROLE_MANAGER');
    }
  },
  mounted() {
    const path = window.location.pathname;
    const g = (name, params = {}) => this.safeGenerate(name, params);

    const map = {
      inscriptions: [
        g('app_registers_academic_support'),
        g('app_registers')
      ],
      pedagogie: [
        g('app_teacher_index'),
        g('app_student_index'),
        g('app_parent_entity_index'),
        g('app_study_class_index'),
        g('app_room_index'),
        g('app_book_list')
      ],
      finances: [
        g('payments_list'),
        g('app_invoice_index')
      ]
    };

    for (const key of Object.keys(map)) {
      this.open[key] = map[key].some(h => this.pathMatch(path, h));
    }

    if (window.innerWidth >= 1024) {
      this.isSidebarOpen = true;
    }
  },
  methods: {
    // Génère l’URL via $routing.generate avec fallback en dev
    safeGenerate(name, params = {}) {
      try {
        if (this.$routing && typeof this.$routing.generate === 'function') {
          return this.$routing.generate(name, params);
        }
      } catch {}
      // Fallback simple si $routing n’est pas dispo (dev)
      return `/${name}`;
    },
    // Teste si la route est active (parnom + params)
    isActiveRoute(name, params = {}) {
      const href = this.safeGenerate(name, params);
      return this.pathMatch(window.location.pathname, href);
    },
    // Comparaison pathname vs href
    pathMatch(pathname, href) {
      if (!href || href === '#') return false;
      try {
        const a = document.createElement('a');
        a.href = href;
        const hrefPath = a.pathname || href;
        const norm = s => (s || '/').replace(/\/+$/, '') || '/';
        return norm(pathname).startsWith(norm(hrefPath));
      } catch {
        return pathname.startsWith(href);
      }
    },
    toggle(key) {
      this.open[key] = !this.open[key];
      Object.keys(this.open).forEach(k => {
        if (k !== key) this.open[k] = false;
      });
    },
    toggleSidebar() {
      this.isSidebarOpen = !this.isSidebarOpen;
    }
  }
};
</script>

<style>
/* === Variables CSS : Palette Blanc & Vert Clair === */
:root {
  --sidebar-width: 280px;
  --sidebar-bg: #ffffff;
  --sidebar-text: #334155; /* Gris-bleu foncé */
  --brand-color: #4ADE80; /* Vert clair vif */
  --brand-color-light: #F0FDF4; /* Vert très clair pour les fonds */
  --border-color: #e2e8f0; /* Gris clair */
  --submenu-text: #64748b; /* Gris moyen */
  --header-height: 80px;
  --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* === Reset === */
.list-unstyled {
  list-style: none;
  padding: 0;
  margin: 0;
}

/* === Bouton Toggle (Mobile) === */
.sidebar-toggle {
  position: fixed;
  top: 16px;
  left: 16px;
  z-index: 1100;
  display: none;
  background: var(--sidebar-bg);
  color: var(--sidebar-text);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  width: 44px;
  height: 44px;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transition: var(--transition);
}
.sidebar-toggle:hover {
  background: var(--brand-color-light);
  color: var(--brand-color);
  transform: scale(1.05);
}
.sidebar-toggle i {
  font-size: 20px;
}

/* === Overlay (Mobile) === */
.sidebar-overlay {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.4);
  z-index: 999;
  backdrop-filter: blur(3px);
}

/* === Sidebar === */
.modern-sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: var(--sidebar-width);
  height: 100vh;
  background: var(--sidebar-bg);
  color: var(--sidebar-text);
  overflow-y: auto;
  overflow-x: hidden;
  transition: transform 0.3s ease-out;
  z-index: 1000;
  border-right: 1px solid var(--border-color);
}
.modern-sidebar::-webkit-scrollbar { width: 6px; }
.modern-sidebar::-webkit-scrollbar-track { background: transparent; }
.modern-sidebar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }
.modern-sidebar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }

/* === Header === */
.sidebar-header {
  padding: 24px 20px;
  height: var(--header-height);
  display: flex;
  align-items: center;
}
.logo-container {
  display: flex;
  align-items: center;
  gap: 16px;
}

/* === Menu === */
.sidebar-menu {
  padding: 8px 12px;
}
.menu-header {
  padding: 24px 12px 8px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #94a3b8;
}
.menu-link, .submenu-toggle {
  display: flex;
  align-items: center;
  padding: 10px 12px;
  margin: 4px 0;
  border-radius: 8px;
  color: var(--sidebar-text);
  text-decoration: none;
  transition: var(--transition);
  position: relative;
  border: none;
  background: none;
  width: 100%;
  text-align: left;
  cursor: pointer;
  font-size: 15px;
  font-weight: 500;
}
.menu-link:hover, .submenu-toggle:hover {
  background: var(--brand-color-light);
  color: var(--brand-color);
}
.menu-link.active, .submenu-toggle.active {
  background: var(--brand-color-light);
  color: var(--brand-color);
  font-weight: 600;
}
.menu-icon {
  font-size: 18px;
  margin-right: 16px;
  min-width: 20px;
  text-align: center;
}
.menu-text {
  flex: 1;
}

/* === Sous-menu === */
.toggle-icon {
  font-size: 14px;
  transition: transform 0.3s ease;
  opacity: 0.7;
}
.has-submenu.open > .submenu-toggle .toggle-icon {
  transform: rotate(180deg);
}
.submenu {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.35s ease-in-out;
  padding-left: 20px;
}
.has-submenu.open .submenu {
  max-height: 500px; /* Ajuster si sous-menu très long */
}
.submenu li a {
  display: flex;
  align-items: center;
  padding: 10px 12px 10px 32px;
  color: var(--submenu-text);
  text-decoration: none;
  font-size: 14px;
  transition: var(--transition);
  position: relative;
}
.submenu-dot {
  position: absolute;
  left: 12px;
  width: 5px;
  height: 5px;
  background: #cbd5e1;
  border-radius: 50%;
  transition: var(--transition);
}
.submenu li a:hover {
  color: var(--brand-color);
}
.submenu li a:hover .submenu-dot {
  background: var(--brand-color);
  transform: scale(1.3);
}
.submenu li a.active {
  color: var(--brand-color);
  font-weight: 500;
}
.submenu li a.active .submenu-dot {
  background: var(--brand-color);
  transform: scale(1.3);
}

/* === Responsive === */
@media (max-width: 1023px) {
  .sidebar-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .sidebar-overlay {
    display: block;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
  }
  .modern-sidebar.open + .sidebar-overlay {
    opacity: 1;
    pointer-events: all;
  }
  .modern-sidebar {
    transform: translateX(-100%);
    box-shadow: 4px 0 15px rgba(0,0,0,0.1);
    border-right: none;
  }
  .modern-sidebar.open {
    transform: translateX(0);
  }
}
@media (min-width: 1024px) {
  .modern-sidebar {
    transform: translateX(0);
  }
}
</style>
