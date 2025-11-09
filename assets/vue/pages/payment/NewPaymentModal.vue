<template>
  <div class="modal fade" id="newPaiementsModal" tabindex="-1" aria-labelledby="newPaiementsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content modern-modal">
        <!-- Header moderne avec gradient -->
        <div class="modal-header-modern">
          <div class="header-content">
            <div class="icon-circle">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                <line x1="1" y1="10" x2="23" y2="10"/>
              </svg>
            </div>
            <div class="header-text">
              <h5 class="modal-title">Nouveau Paiement</h5>
              <p class="modal-subtitle">Enregistrer un nouveau paiement</p>
            </div>
          </div>
          <button type="button" class="btn-close-modern" data-bs-dismiss="modal" aria-label="Close">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M15 5L5 15M5 5l10 10"/>
            </svg>
          </button>
        </div>

        <div class="modal-body-modern">
          <form id="newPaymentForm">

            <!-- Étape 1: Type de paiement -->
            <div class="step-container" :class="{ 'step-active': true }">
              <div class="step-header-modern">
                <div class="step-badge">1</div>
                <div class="step-info">
                  <h6 class="step-title-modern">Type de paiement</h6>
                  <p class="step-description">Sélectionnez le type de service</p>
                </div>
              </div>

              <div class="payment-types-grid">
                <div
                    class="payment-type-option"
                    :class="{ selected: paymentType === 'arabe' }"
                    @click="paymentType = 'arabe'"
                >
                  <div class="option-icon arabe-gradient">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M5 8h14M5 8a2 2 0 0 1 0-4h14a2 2 0 1 1 0 4M5 8v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8m-9 4h4"/>
                    </svg>
                  </div>
                  <div class="option-content">
                    <h6 class="option-title">Cours d'Arabe</h6>
                    <p class="option-desc">Paiement pour les cours de langue arabe</p>
                  </div>
                  <div class="option-check">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M16.707 5.293a1 1 0 0 1 0 1.414l-8 8a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 1.414-1.414L8 12.586l7.293-7.293a1 1 0 0 1 1.414 0z"/>
                    </svg>
                  </div>
                </div>

                <div
                    class="payment-type-option"
                    :class="{ selected: paymentType === 'soutien' }"
                    @click="paymentType = 'soutien'"
                >
                  <div class="option-icon soutien-gradient">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                      <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>
                  </div>
                  <div class="option-content">
                    <h6 class="option-title">Soutien Scolaire</h6>
                    <p class="option-desc">Paiement pour le soutien scolaire</p>
                  </div>
                  <div class="option-check">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M16.707 5.293a1 1 0 0 1 0 1.414l-8 8a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 1.414-1.414L8 12.586l7.293-7.293a1 1 0 0 1 1.414 0z"/>
                    </svg>
                  </div>
                </div>

                <div
                    class="payment-type-option"
                    :class="{ selected: paymentType === 'livres' }"
                    @click="paymentType = 'livres'"
                >
                  <div class="option-icon livres-gradient">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                      <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                    </svg>
                  </div>
                  <div class="option-content">
                    <h6 class="option-title">Livres</h6>
                    <p class="option-desc">Achat de livres scolaires</p>
                  </div>
                  <div class="option-check">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M16.707 5.293a1 1 0 0 1 0 1.414l-8 8a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 1.414-1.414L8 12.586l7.293-7.293a1 1 0 0 1 1.414 0z"/>
                    </svg>
                  </div>
                </div>
              </div>
            </div>

            <!-- Étape 2: Sélection parent -->
            <div v-if="paymentType" class="step-container" :class="{ 'step-active': paymentType }">
              <div class="step-header-modern">
                <div class="step-badge">2</div>
                <div class="step-info">
                  <h6 class="step-title-modern">Sélectionner un parent</h6>
                  <p class="step-description">Recherchez et choisissez le parent</p>
                </div>
              </div>

              <div class="search-box-modern">
                <div class="search-input-wrapper">
                  <svg class="search-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M9 17A8 8 0 1 0 9 1a8 8 0 0 0 0 16zM19 19l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                  <input
                      type="text"
                      class="search-input-modern"
                      v-model="parentSearchQuery"
                      placeholder="Tapez le nom du parent..."
                      @focus="dropdownVisible = true"
                      @blur="hideDropdown"
                      @input="onParentInput"
                  />
                </div>

                <!-- Dropdown résultats -->
                <div v-if="dropdownVisible && parentSearchQuery" class="search-results-dropdown">
                  <div v-for="p in filteredParents" :key="p.id"
                       class="result-item"
                       @mousedown.prevent="selectParent(p)">
                    <div class="result-avatar">
                      <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm-7 9a7 7 0 1 1 14 0H3z"/>
                      </svg>
                    </div>
                    <div class="result-info">
                      <div class="result-name">{{ p.fullNameParent }}</div>
                      <div class="result-meta">{{ p.students.length }} enfant(s)</div>
                    </div>
                    <svg class="result-check" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M16.707 5.293a1 1 0 0 1 0 1.414l-8 8a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 1.414-1.414L8 12.586l7.293-7.293a1 1 0 0 1 1.414 0z"/>
                    </svg>
                  </div>
                  <div v-if="filteredParents.length === 0" class="no-results-modern">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                      <circle cx="11" cy="11" r="8"/>
                      <path d="M21 21l-4.35-4.35"/>
                    </svg>
                    <p>Aucun parent trouvé</p>
                  </div>
                </div>
              </div>

              <!-- Parent sélectionné -->
              <div v-if="selectedParent" class="selected-badge-modern">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm3.707-9.293a1 1 0 0 0-1.414-1.414L9 10.586 7.707 9.293a1 1 0 0 0-1.414 1.414l2 2a1 1 0 0 0 1.414 0l4-4z"/>
                </svg>
                <span><strong>Parent sélectionné :</strong> {{ selectedParent.fullNameParent }}</span>
              </div>
            </div>

            <!-- Étape 3: Sélection des enfants -->
            <div v-if="selectedParent && selectedParent.students.length" class="step-container" :class="{ 'step-active': selectedParent }">
              <div class="step-header-modern">
                <div class="step-badge">3</div>
                <div class="step-info">
                  <h6 class="step-title-modern">{{ isBooks ? 'Choisir l\'enfant' : 'Choisir les enfants' }}</h6>
                  <p class="step-description">{{ isBooks ? 'Sélectionnez un seul enfant' : 'Sélectionnez un ou plusieurs enfants' }}</p>
                </div>
              </div>

              <!-- Cas livres : sélection unique -->
              <div v-if="isBooks" class="students-grid-modern">
                <div
                    v-for="s in selectedParent.students"
                    :key="s.id"
                    class="student-card-modern"
                    :class="{ selected: selectedChildForBooks === s.id }"
                    @click="selectedChildForBooks = s.id; onBooksStudentChange()"
                >
                  <div class="student-avatar-modern">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z"/>
                    </svg>
                  </div>
                  <div class="student-details">
                    <h6 class="student-name-modern">{{ s.firstName }} {{ s.lastName }}</h6>
                    <div class="student-level-badge">{{ s.levelClass || 'Niveau non défini' }}</div>
                  </div>
                  <div class="selection-indicator">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M16.707 5.293a1 1 0 0 1 0 1.414l-8 8a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 1.414-1.414L8 12.586l7.293-7.293a1 1 0 0 1 1.414 0z"/>
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Cas arabe/soutien : sélection multiple -->
              <div v-else class="students-accordion-modern">
                <div v-for="s in selectedParent.students" :key="s.id" class="accordion-item-modern">
                  <div
                      class="accordion-header-modern"
                      :class="{ expanded: selectedChildren.includes(s.id) }"
                      @click="toggleStudent(s.id)"
                  >
                    <div class="header-left">
                      <input
                          type="checkbox"
                          class="checkbox-modern"
                          :checked="selectedChildren.includes(s.id)"
                          @click.stop
                          @change="toggleStudent(s.id)"
                      />
                      <div class="student-avatar-small">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                          <path d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z"/>
                        </svg>
                      </div>
                      <div class="student-info-compact">
                        <h6 class="student-name-compact">{{ s.firstName }} {{ s.lastName }}</h6>
                        <span class="student-level-compact">{{ s.levelClass }}</span>
                      </div>
                    </div>
                    <svg class="expand-icon" width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M5.293 7.293a1 1 0 0 1 1.414 0L10 10.586l3.293-3.293a1 1 0 1 1 1.414 1.414l-4 4a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 0-1.414z"/>
                    </svg>
                  </div>

                  <!-- Matières pour soutien scolaire -->
                  <div v-if="paymentType==='soutien' && selectedChildren.includes(s.id)" class="accordion-content-modern">
                    <div class="subjects-header">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                      </svg>
                      <span>Matières disponibles</span>
                    </div>

                    <div v-if="s.registrations.length" class="subjects-list-modern">
                      <label
                          v-for="r in s.registrations"
                          :key="r.studyClass.id"
                          class="subject-checkbox-card"
                      >
                        <input
                            type="checkbox"
                            class="checkbox-modern"
                            :value="r.studyClass"
                            v-model="selectedClasses[s.id]"
                            @change="calculateTotal"
                        />
                        <div class="subject-info">
                          <span class="subject-name">{{ r.studyClass.speciality }}</span>
                          <span class="subject-price-tag">25€/mois</span>
                        </div>
                      </label>
                    </div>
                    <div v-else class="no-subjects-modern">
                      <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 8v4m0 4h.01"/>
                      </svg>
                      <span>Aucune matière inscrite</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Étape 4: Catalogue de livres -->
            <div v-if="isBooks && selectedChildForBooks" class="step-container" :class="{ 'step-active': selectedChildForBooks }">
              <div class="step-header-modern">
                <div class="step-badge">4</div>
                <div class="step-info">
                  <h6 class="step-title-modern">
                    Catalogue de livres
                    <span class="level-indicator">{{ currentStudentLevel || 'Tous niveaux' }}</span>
                  </h6>
                  <p class="step-description">Sélectionnez les livres à acheter</p>
                </div>
              </div>

              <!-- Barre de recherche -->
              <div class="books-search-modern">
                <svg class="search-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                  <path d="M9 17A8 8 0 1 0 9 1a8 8 0 0 0 0 16zM19 19l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <input
                    type="text"
                    class="search-input-modern"
                    placeholder="Rechercher un livre..."
                    v-model.trim="bookSearch"
                />
              </div>

              <!-- Grille de livres -->
              <div class="books-grid-modern">
                <div v-for="b in filteredBooksByStudent" :key="b.id" class="book-card-modern">
                  <div class="book-image-wrapper">
                    <img v-if="b.imageName" :src="bookImage(b.imageName)" :alt="b.name" class="book-image" />
                    <div v-else class="book-placeholder-modern">
                      <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                      </svg>
                    </div>
                  </div>

                  <div class="book-info-wrapper">
                    <div class="book-header-row">
                      <h6 class="book-title-modern">{{ b.name }}</h6>
                      <div class="book-price-badge">{{ asMoney(b.salePrice) }} €</div>
                    </div>

                    <p v-if="b.description" class="book-desc-modern">
                      {{ truncate(b.description, 80) }}
                    </p>

                    <div class="book-level-tag">{{ b.level || 'Tous niveaux' }}</div>

                    <div class="book-quantity-controls">
                      <button
                          type="button"
                          class="qty-btn"
                          @click="decreaseQty(b)"
                          :disabled="getSelectedQty(b.id) === 0"
                      >
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                          <path d="M4 8h8"/>
                        </svg>
                      </button>
                      <span class="qty-display">{{ getSelectedQty(b.id) }}</span>
                      <button
                          type="button"
                          class="qty-btn"
                          @click="increaseQty(b)"
                      >
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                          <path d="M8 4v8M4 8h8"/>
                        </svg>
                      </button>
                    </div>

                    <div v-if="getSelectedQty(b.id) > 0" class="book-line-total">
                      Total : <strong>{{ asMoney(lineTotal(b)) }} €</strong>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Résumé panier -->
              <div v-if="selectedBooks.length" class="cart-summary-modern">
                <div class="cart-header">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"/>
                    <circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                  </svg>
                  <span>Livres sélectionnés ({{ selectedBooks.length }})</span>
                </div>
                <div class="cart-items">
                  <div v-for="item in selectedBooksDetails" :key="item.bookId" class="cart-item">
                    <span class="cart-book-name">{{ item.name }}</span>
                    <span class="cart-qty">× {{ item.quantity }}</span>
                    <span class="cart-price">{{ asMoney(item.total) }} €</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Étape finale: Détails du paiement -->
            <div v-if="showDetails" class="step-container" :class="{ 'step-active': showDetails }">
              <div class="step-header-modern">
                <div class="step-badge">{{ isBooks ? '5' : '4' }}</div>
                <div class="step-info">
                  <h6 class="step-title-modern">Détails du paiement</h6>
                  <p class="step-description">Finalisez les informations de paiement</p>
                </div>
              </div>

              <div class="payment-details-grid">
                <!-- Colonne gauche : Montants -->
                <div class="payment-amounts-card">
                  <div class="amounts-header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <line x1="12" y1="1" x2="12" y2="23"/>
                      <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                    <span>Montants</span>
                  </div>

                  <div class="amount-row">
                    <span class="amount-label">Montant total</span>
                    <span class="amount-value">{{ asMoney(totalAmount) }} €</span>
                  </div>

                  <div class="form-group-modern">
                    <label class="label-modern">Remise (€)</label>
                    <input
                        type="number"
                        step="0.01"
                        class="input-modern"
                        v-model.number="discount"
                        placeholder="0.00"
                    />
                  </div>

                  <div v-if="discount > 0" class="amount-row discount-row">
                    <span class="amount-label">Remise</span>
                    <span class="amount-value discount">-{{ asMoney(discount) }} €</span>
                  </div>

                  <div class="amount-row total-row">
                    <span class="amount-label">Net à payer</span>
                    <span class="amount-value total">{{ asMoney(Math.max(0, totalAmount - discount)) }} €</span>
                  </div>

                  <div class="form-group-modern">
                    <label class="label-modern">Montant payé (€) *</label>
                    <input
                        type="number"
                        step="0.01"
                        class="input-modern input-large"
                        v-model.number="paidAmount"
                        placeholder="0.00"
                        required
                    />
                  </div>
                </div>

                <!-- Colonne droite : Méthode et détails -->
                <div class="payment-method-card">
                  <div class="method-header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="1" y="4" width="22" height="16" rx="2"/>
                      <line x1="1" y1="10" x2="23" y2="10"/>
                    </svg>
                    <span>Méthode de paiement *</span>
                  </div>

                  <div class="payment-methods-grid">
                    <div
                        class="method-option"
                        :class="{ selected: paymentMethod === 'Espèces' }"
                        @click="paymentMethod = 'Espèces'"
                    >
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"/>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                      </svg>
                      <span>Espèces</span>
                    </div>
                    <div
                        class="method-option"
                        :class="{ selected: paymentMethod === 'Carte bancaire' }"
                        @click="paymentMethod = 'Carte bancaire'"
                    >
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="4" width="22" height="16" rx="2"/>
                        <line x1="1" y1="10" x2="23" y2="10"/>
                      </svg>
                      <span>Carte</span>
                    </div>
                    <div
                        class="method-option"
                        :class="{ selected: paymentMethod === 'Chèque' }"
                        @click="paymentMethod = 'Chèque'"
                    >
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                      </svg>
                      <span>Chèque</span>
                    </div>
                  </div>

                  <!-- Champs spécifiques soutien scolaire -->
                  <div v-if="paymentType==='soutien'" class="form-section-modern">
                    <label class="label-modern">Mois (plusieurs) *</label>
                    <div class="months-selector">
                      <label
                          v-for="m in months"
                          :key="m"
                          class="month-option"
                          :class="{ selected: selectedMonths.includes(m) }"
                      >
                        <input
                            type="checkbox"
                            class="d-none"
                            :value="m"
                            v-model="selectedMonths"
                        />
                        <span>{{ m }}</span>
                      </label>
                    </div>

                    <div class="form-group-modern mt-3">
                      <label class="label-modern">Année *</label>
                      <select v-model="selectedYear" class="select-modern" required>
                        <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
                      </select>
                    </div>
                  </div>

                  <div class="form-group-modern">
                    <label class="label-modern">Commentaire</label>
                    <textarea
                        v-model="comment"
                        class="textarea-modern"
                        rows="3"
                        placeholder="Ajouter un commentaire (optionnel)"
                    ></textarea>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>

        <!-- Footer moderne -->
        <div class="modal-footer-modern">
          <button
              type="button"
              class="btn-cancel-modern"
              data-bs-dismiss="modal"
          >
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M13.5 4.5l-9 9M4.5 4.5l9 9"/>
            </svg>
            <span>Annuler</span>
          </button>
          <button
              type="submit"
              class="btn-submit-modern"
              @click.prevent="submitPayment"
              :disabled="isSubmitDisabled"
          >
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M15 5l-8 8-4-4"/>
            </svg>
            <span v-if="isSubmitDisabled">Compléter les informations</span>
            <span v-else>Enregistrer le paiement</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NewPaymentModal',
  props: {
    parents: { type: Array, required: true },
    books: { type: Array, required: true }
  },
  data() {
    return {
      dropdownVisible: false,
      parentSearchQuery: '',
      selectedParent: null,
      selectedChildren: [],
      selectedClasses: {},
      selectedChildForBooks: null,
      selectedBooks: [],
      bookSearch: '',
      totalAmount: 0,
      paidAmount: 0,
      discount: 0,
      selectedMonths: [],
      paymentMethod: '',
      paymentType: '',
      selectedYear: 2025,
      comment: '',
      availableYears: [2023, 2024, 2025, 2026],
      months: ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
    };
  },
  computed: {
    showDetails() {
      if (!this.paymentType) return false;
      if (this.isBooks) {
        return this.selectedBooks.length > 0;
      }
      return !!this.selectedParent;
    },
    isBooks() { return this.paymentType === 'livres'; },

    filteredParents() {
      const q = this.parentSearchQuery.trim().toLowerCase();
      return q ? this.parents.filter(p => (p.fullNameParent || '').toLowerCase().includes(q)) : this.parents;
    },

    currentStudent() {
      if (!this.selectedParent || !this.isBooks || !this.selectedChildForBooks) return null;
      return this.selectedParent.students.find(s => s.id === this.selectedChildForBooks) || null;
    },

    currentStudentLevel() {
      return this.currentStudent?.levelClass || null;
    },

    filteredBooksByStudent() {
      const level = this.currentStudentLevel;
      let list = Array.isArray(this.books) ? this.books : [];
      if (level) list = list.filter(b => (b.level || '') === level);
      const q = this.bookSearch.trim().toLowerCase();
      if (q) list = list.filter(b =>
          (b.name || '').toLowerCase().includes(q) ||
          (b.description || '').toLowerCase().includes(q)
      );
      return list;
    },

    selectedBooksDetails() {
      return this.selectedBooks.map(item => {
        const book = this.books.find(b => b.id === item.bookId);
        return {
          ...item,
          name: book?.name || 'Livre inconnu',
          total: item.quantity * item.unitPrice
        };
      });
    },

    isSubmitDisabled() {
      if (this.isBooks) {
        return !(this.selectedParent && this.selectedChildForBooks && this.selectedBooks.length > 0
            && this.paidAmount > 0 && this.paymentMethod);
      }
      if (this.paymentType === 'soutien') {
        return this.selectedMonths.length === 0
            || !this.paidAmount || this.paidAmount <= 0
            || !this.paymentMethod
            || !this.selectedChildren.length
            || Object.keys(this.selectedClasses).length === 0;
      }
      return !this.paidAmount || this.paidAmount <= 0 || !this.paymentMethod || !this.selectedChildren.length;
    },
  },

  watch: {
    selectedMonths() { if (this.paymentType === 'soutien') this.calculateTotal(); },
    paymentType(newVal) {
      this.discount = 0;
      this.paidAmount = 0;
      this.totalAmount = 0;

      if (newVal === 'livres') {
        this.selectedChildren = [];
        this.selectedClasses = {};
        this.selectedMonths = [];
      } else {
        this.selectedChildForBooks = null;
        this.selectedBooks = [];
        this.bookSearch = '';
      }
    },
    selectedClasses: { deep: true, handler() { if (!this.isBooks) this.calculateTotal(); } },
    selectedChildren() { if (!this.isBooks) this.calculateTotal(); },
    selectedBooks: {
      deep: true,
      handler() {
        if (this.isBooks) {
          const sum = this.selectedBooks.reduce((acc, it) => acc + (Number(it.unitPrice || 0) * Number(it.quantity || 0)), 0);
          this.totalAmount = Number(sum.toFixed(2));
          const paid = Math.max(0, this.totalAmount - Number(this.discount || 0));
          this.paidAmount = Number(paid.toFixed(2));
        }
      }
    },
    discount() {
      if (this.isBooks) {
        const paid = Math.max(0, this.totalAmount - Number(this.discount || 0));
        this.paidAmount = Number(paid.toFixed(2));
      }
    }
  },

  methods: {
    onParentInput() { this.dropdownVisible = true; },

    selectParent(p) {
      this.selectedParent = p;
      this.parentSearchQuery = p.fullNameParent;
      this.dropdownVisible = false;
      this.selectedChildren = [];
      this.selectedClasses = {};
      this.selectedChildForBooks = null;
      this.selectedBooks = [];
      this.totalAmount = this.paidAmount = this.discount = 0;
    },

    hideDropdown() {
      setTimeout(() => { this.dropdownVisible = false; }, 200);
    },

    toggleStudent(studentId) {
      const index = this.selectedChildren.indexOf(studentId);
      if (index > -1) {
        this.selectedChildren.splice(index, 1);
        const copy = { ...this.selectedClasses };
        delete copy[studentId];
        this.selectedClasses = copy;
      } else {
        this.selectedChildren.push(studentId);
        if (!this.selectedClasses[studentId]) {
          this.selectedClasses = { ...this.selectedClasses, [studentId]: [] };
        }
      }
      this.calculateTotal();
    },

    updateSelectedClasses(studentId, checked) {
      if (checked) {
        if (!this.selectedClasses[studentId]) {
          this.selectedClasses = { ...this.selectedClasses, [studentId]: [] };
        }
      } else {
        const copy = { ...this.selectedClasses };
        delete copy[studentId];
        this.selectedClasses = copy;
        this.calculateTotal();
      }
    },

    calculateTotal() {
      if (this.isBooks) return;

      const pricePerClassPerMonth = 25;
      const totalClasses = Object.values(this.selectedClasses)
          .reduce((acc, classes) => acc + (classes?.length || 0), 0);
      const monthsCount = this.paymentType === 'soutien' ? this.selectedMonths.length : 1;

      this.totalAmount = totalClasses * pricePerClassPerMonth * monthsCount;

      const net = Math.max(0, this.totalAmount - Number(this.discount || 0));
      this.paidAmount = Number(net.toFixed(2));
    },

    onBooksStudentChange() {
      this.selectedBooks = [];
      this.bookSearch = '';
    },

    getSelectedQty(bookId) {
      const it = this.selectedBooks.find(i => i.bookId === bookId);
      return it ? it.quantity : 0;
    },

    increaseQty(book) {
      this.setSelectedQty(book, this.getSelectedQty(book.id) + 1);
    },

    decreaseQty(book) {
      const current = this.getSelectedQty(book.id);
      if (current > 0) {
        this.setSelectedQty(book, current - 1);
      }
    },

    setSelectedQty(book, quantity) {
      let q = Number(quantity);
      if (!Number.isFinite(q) || q < 0) q = 0;

      const idx = this.selectedBooks.findIndex(i => i.bookId === book.id);
      if (q === 0) {
        if (idx !== -1) this.selectedBooks.splice(idx, 1);
        return;
      }

      const unit = Number(book.salePrice || 0);
      if (idx === -1) {
        this.selectedBooks.push({
          bookId: book.id,
          quantity: q,
          unitPrice: Number(unit.toFixed(2))
        });
      } else {
        this.selectedBooks[idx].quantity = q;
        this.selectedBooks[idx].unitPrice = Number(unit.toFixed(2));
      }
    },

    lineTotal(book) {
      const q = this.getSelectedQty(book.id);
      const unit = Number(book.salePrice || 0);
      return Number((q * unit).toFixed(2));
    },

    bookImage(name) {
      return `/uploads/books/${name}`;
    },

    truncate(s, n=100) {
      if(!s) return '';
      const t=String(s);
      return t.length>n? t.slice(0,n-1)+'…' : t;
    },

    asMoney(v) {
      const n = Number(v);
      return Number.isFinite(n) ? n.toFixed(2).replace('.', ',') : '0,00';
    },

    async submitPayment() {
      if (this.isSubmitDisabled) {
        alert('Veuillez remplir tous les champs requis.');
        return;
      }

      const base = {
        parent: this.selectedParent,
        paidAmount: Number(this.paidAmount),
        discount: Number(this.discount || 0),
        paymentMethod: this.paymentMethod,
        paymentType: this.paymentType,
        comment: this.comment,
        selectedYear: this.selectedYear,
      };

      let payload;

      if (this.isBooks) {
        payload = {
          ...base,
          selectedChildren: [{ id: this.selectedChildForBooks }],
          books: this.selectedBooks.map(b => ({
            bookId: b.bookId,
            quantity: b.quantity,
            unitPrice: b.unitPrice
          })),
        };
      } else {
        payload = {
          ...base,
          selectedMonths: this.paymentType === 'soutien' ? (this.selectedMonths || []) : [],
          selectedMonth: null,
          selectedChildren: this.selectedChildren.map(id => ({
            id,
            classes: this.selectedClasses[id] || []
          })),
        };
      }

      try {
        const { data } = await this.axios.post(this.$routing.generate("payment_new"), payload);

        const invId  = data?.invoiceId;
        const invUrl = data?.invoiceUrl || (invId ? this.$routing.generate("app_invoice_show", { id: invId }) : null);
        if (invUrl) {
          window.open(invUrl, "_blank", "noopener");
        }

        this.$bootstrap.Modal.getInstance(document.getElementById("newPaiementsModal"))?.hide();

        this.$emit("payment-added");
        this.resetForm();
      } catch (e) {
        console.error(e);
        alert("Erreur lors de l'enregistrement du paiement.");
      }
    },

    resetForm() {
      Object.assign(this.$data, this.$options.data());
    }
  }
};
</script>

<style scoped>
/* Variables */
:root {
  --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
  --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  --modal-border-radius: 16px;
  --card-border-radius: 12px;
  --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Modal moderne */
.modern-modal {
  border: none;
  border-radius: var(--modal-border-radius);
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}

/* Header */
.modal-header-modern {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 1.75rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border: none;
}

.header-content {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.icon-circle {
  width: 48px;
  height: 48px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.header-text {
  color: white;
}

.modal-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
}

.modal-subtitle {
  font-size: 0.875rem;
  margin: 0;
  opacity: 0.9;
}

.btn-close-modern {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: var(--transition-smooth);
  color: white;
}

.btn-close-modern:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

/* Body */
.modal-body-modern {
  padding: 2rem;
  max-height: calc(100vh - 250px);
  overflow-y: auto;
}

/* Scrollbar personnalisée */
.modal-body-modern::-webkit-scrollbar {
  width: 8px;
}

.modal-body-modern::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}

.modal-body-modern::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.modal-body-modern::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Conteneurs d'étapes */
.step-container {
  margin-bottom: 2.5rem;
  opacity: 0;
  transform: translateY(20px);
  animation: fadeInUp 0.5s ease forwards;
}

.step-container.step-active {
  animation: fadeInUp 0.5s ease forwards;
}

@keyframes fadeInUp {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.step-header-modern {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.step-badge {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.125rem;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.step-info {
  flex: 1;
}

.step-title-modern {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 0.25rem 0;
}

.step-description {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0;
}

.level-indicator {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 20px;
  font-size: 0.813rem;
  margin-left: 0.5rem;
}

/* Types de paiement */
.payment-types-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1rem;
}

.payment-type-option {
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: var(--card-border-radius);
  padding: 1.5rem;
  cursor: pointer;
  transition: var(--transition-smooth);
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.payment-type-option:hover {
  border-color: #667eea;
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(102, 126, 234, 0.15);
}

.payment-type-option.selected {
  border-color: #667eea;
  background: linear-gradient(135deg, #f8f9ff 0%, #f3f4ff 100%);
  box-shadow: 0 8px 24px rgba(102, 126, 234, 0.2);
}

.option-icon {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.arabe-gradient {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.soutien-gradient {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}

.livres-gradient {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.option-content {
  flex: 1;
}

.option-title {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

.option-desc {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0;
  line-height: 1.5;
}

.option-check {
  position: absolute;
  top: 1rem;
  right: 1rem;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #667eea;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transform: scale(0);
  transition: var(--transition-smooth);
}

.payment-type-option.selected .option-check {
  opacity: 1;
  transform: scale(1);
}

/* Recherche parent */
.search-box-modern {
  position: relative;
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 1rem;
  color: #94a3b8;
  z-index: 1;
}

.search-input-modern {
  width: 100%;
  padding: 0.875rem 1rem 0.875rem 3rem;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  font-size: 1rem;
  transition: var(--transition-smooth);
  background: white;
}

.search-input-modern:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.search-results-dropdown {
  position: absolute;
  top: calc(100% + 0.5rem);
  left: 0;
  right: 0;
  background: white;
  border-radius: var(--card-border-radius);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
  max-height: 320px;
  overflow-y: auto;
  z-index: 1000;
  border: 1px solid #e2e8f0;
}

.result-item {
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  transition: var(--transition-smooth);
  border-bottom: 1px solid #f1f5f9;
}

.result-item:last-child {
  border-bottom: none;
}

.result-item:hover {
  background: #f8fafc;
}

.result-avatar {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
}

.result-info {
  flex: 1;
}

.result-name {
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 0.125rem;
}

.result-meta {
  font-size: 0.813rem;
  color: #64748b;
}

.result-check {
  color: #10b981;
  opacity: 0;
}

.result-item:hover .result-check {
  opacity: 1;
}

.no-results-modern {
  padding: 3rem 2rem;
  text-align: center;
  color: #94a3b8;
}

.no-results-modern svg {
  margin-bottom: 1rem;
}

.no-results-modern p {
  margin: 0;
  font-size: 0.938rem;
}

/* Badge sélectionné */
.selected-badge-modern {
  margin-top: 1rem;
  padding: 1rem 1.25rem;
  background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
  border-radius: var(--card-border-radius);
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: #065f46;
  font-size: 0.938rem;
  animation: fadeIn 0.3s ease;
}

.selected-badge-modern svg {
  color: #10b981;
  flex-shrink: 0;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Grille d'étudiants (livres) */
.students-grid-modern {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1rem;
}

.student-card-modern {
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: var(--card-border-radius);
  padding: 1.5rem;
  cursor: pointer;
  transition: var(--transition-smooth);
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 1rem;
  position: relative;
}

.student-card-modern:hover {
  border-color: #667eea;
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(102, 126, 234, 0.15);
}

.student-card-modern.selected {
  border-color: #667eea;
  background: linear-gradient(135deg, #f8f9ff 0%, #f3f4ff 100%);
  box-shadow: 0 8px 24px rgba(102, 126, 234, 0.2);
}

.student-avatar-modern {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #e0e7ff 0%, #ddd6fe 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #667eea;
}

.student-card-modern.selected .student-avatar-modern {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.student-details {
  flex: 1;
}

.student-name-modern {
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
  font-size: 1rem;
}

.student-level-badge {
  display: inline-block;
  padding: 0.375rem 0.875rem;
  background: #f1f5f9;
  color: #475569;
  border-radius: 20px;
  font-size: 0.813rem;
  font-weight: 500;
}

.student-card-modern.selected .student-level-badge {
  background: rgba(102, 126, 234, 0.15);
  color: #667eea;
}

.selection-indicator {
  position: absolute;
  top: 1rem;
  right: 1rem;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #667eea;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transform: scale(0);
  transition: var(--transition-smooth);
}

.student-card-modern.selected .selection-indicator {
  opacity: 1;
  transform: scale(1);
}

/* Accordéon étudiants (soutien/arabe) */
.students-accordion-modern {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.accordion-item-modern {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: var(--card-border-radius);
  overflow: hidden;
  transition: var(--transition-smooth);
}

.accordion-item-modern:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.accordion-header-modern {
  padding: 1.25rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  background: #f8fafc;
  transition: var(--transition-smooth);
}

.accordion-header-modern:hover {
  background: #f1f5f9;
}

.accordion-header-modern.expanded {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.checkbox-modern {
  width: 20px;
  height: 20px;
  border: 2px solid #cbd5e1;
  border-radius: 4px;
  cursor: pointer;
  accent-color: #667eea;
}

.student-avatar-small {
  width: 36px;
  height: 36px;
  background: #e0e7ff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #667eea;
}

.accordion-header-modern.expanded .student-avatar-small {
  background: rgba(255, 255, 255, 0.2);
  color: white;
}

.student-info-compact {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.student-name-compact {
  font-weight: 600;
  font-size: 0.938rem;
  margin: 0;
  color: #1e293b;
}

.accordion-header-modern.expanded .student-name-compact {
  color: white;
}

.student-level-compact {
  font-size: 0.813rem;
  color: #64748b;
}

.accordion-header-modern.expanded .student-level-compact {
  color: rgba(255, 255, 255, 0.9);
}

.expand-icon {
  color: #64748b;
  transition: var(--transition-smooth);
}

.accordion-header-modern.expanded .expand-icon {
  color: white;
  transform: rotate(180deg);
}

.accordion-content-modern {
  padding: 1.5rem;
  background: white;
  border-top: 1px solid #f1f5f9;
  animation: slideDown 0.3s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    max-height: 0;
  }
  to {
    opacity: 1;
    max-height: 600px;
  }
}

.subjects-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
  color: #475569;
  font-weight: 600;
  font-size: 0.875rem;
}

.subjects-list-modern {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.subject-checkbox-card {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  background: #f8fafc;
  border-radius: 8px;
  cursor: pointer;
  transition: var(--transition-smooth);
  border: 1px solid transparent;
}

.subject-checkbox-card:hover {
  background: #f1f5f9;
  border-color: #e2e8f0;
}

.subject-checkbox-card:has(.checkbox-modern:checked) {
  background: linear-gradient(135deg, #f8f9ff 0%, #f3f4ff 100%);
  border-color: #667eea;
}

.subject-info {
  flex: 1;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.subject-name {
  font-weight: 500;
  color: #1e293b;
}

.subject-price-tag {
  padding: 0.25rem 0.75rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 20px;
  font-size: 0.813rem;
  font-weight: 600;
}

.no-subjects-modern {
  padding: 2rem;
  text-align: center;
  color: #94a3b8;
}

.no-subjects-modern svg {
  margin-bottom: 0.5rem;
}

/* Recherche et grille de livres */
.books-search-modern {
  position: relative;
  display: flex;
  align-items: center;
  margin-bottom: 1.5rem;
}

.books-search-modern .search-icon {
  position: absolute;
  left: 1rem;
  color: #94a3b8;
}

.books-search-modern .search-input-modern {
  padding-left: 3rem;
}

.books-grid-modern {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.book-card-modern {
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: var(--card-border-radius);
  overflow: hidden;
  transition: var(--transition-smooth);
  display: flex;
  flex-direction: column;
}

.book-card-modern:hover {
  border-color: #667eea;
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(102, 126, 234, 0.15);
}

.book-image-wrapper {
  height: 140px;
  overflow: hidden;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.book-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.book-placeholder-modern {
  color: #cbd5e1;
}

.book-info-wrapper {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  flex: 1;
}

.book-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
}

.book-title-modern {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
  line-height: 1.4;
  flex: 1;
}

.book-price-badge {
  padding: 0.5rem 0.875rem;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.875rem;
  white-space: nowrap;
}

.book-desc-modern {
  font-size: 0.875rem;
  color: #64748b;
  line-height: 1.5;
  margin: 0;
}

.book-level-tag {
  display: inline-block;
  padding: 0.375rem 0.875rem;
  background: #f1f5f9;
  color: #475569;
  border-radius: 20px;
  font-size: 0.813rem;
  font-weight: 500;
  align-self: flex-start;
}

.book-quantity-controls {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-top: 0.5rem;
  padding: 0.75rem;
  background: #f8fafc;
  border-radius: 8px;
}

.qty-btn {
  width: 36px;
  height: 36px;
  border: 2px solid #e2e8f0;
  border-radius: 50%;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: var(--transition-smooth);
  color: #475569;
}

.qty-btn:hover:not(:disabled) {
  border-color: #667eea;
  color: #667eea;
  transform: scale(1.1);
}

.qty-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.qty-display {
  font-size: 1.25rem;
  font-weight: 700;
  color: #667eea;
  min-width: 40px;
  text-align: center;
}

.book-line-total {
  text-align: center;
  padding: 0.75rem;
  background: linear-gradient(135deg, #f8f9ff 0%, #f3f4ff 100%);
  border-radius: 8px;
  color: #667eea;
  font-size: 0.938rem;
}

/* Résumé panier */
.cart-summary-modern {
  margin-top: 2rem;
  background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
  border-radius: var(--card-border-radius);
  padding: 1.5rem;
  border: 2px solid #10b981;
}

.cart-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
  color: #065f46;
  font-weight: 600;
  font-size: 1rem;
}

.cart-header svg {
  color: #10b981;
}

.cart-items {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.cart-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: white;
  border-radius: 8px;
  font-size: 0.875rem;
}

.cart-book-name {
  flex: 1;
  font-weight: 500;
  color: #1e293b;
}

.cart-qty {
  color: #64748b;
  margin: 0 1rem;
}

.cart-price {
  font-weight: 600;
  color: #10b981;
}

/* Détails du paiement */
.payment-details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 1.5rem;
}

.payment-amounts-card,
.payment-method-card {
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: var(--card-border-radius);
  padding: 1.5rem;
}

.amounts-header,
.method-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #f1f5f9;
  color: #1e293b;
  font-weight: 600;
  font-size: 1rem;
}

.amounts-header svg,
.method-header svg {
  color: #667eea;
}

.amount-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid #f1f5f9;
}

.amount-label {
  color: #64748b;
  font-size: 0.938rem;
}

.amount-value {
  font-weight: 600;
  color: #1e293b;
  font-size: 1rem;
}

.discount-row .amount-value.discount {
  color: #10b981;
}

.total-row {
  border-top: 2px solid #667eea;
  border-bottom: none;
  margin-top: 0.5rem;
  padding-top: 1rem;
}

.total-row .amount-label,
.total-row .amount-value {
  color: #667eea;
  font-size: 1.125rem;
  font-weight: 700;
}

/* Formulaires modernes */
.form-group-modern {
  margin-bottom: 1rem;
}

.label-modern {
  display: block;
  font-weight: 600;
  color: #475569;
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

.input-modern,
.select-modern,
.textarea-modern {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.938rem;
  transition: var(--transition-smooth);
  background: white;
}

.input-large {
  padding: 1rem 1.25rem;
  font-size: 1.125rem;
  font-weight: 600;
}

.input-modern:focus,
.select-modern:focus,
.textarea-modern:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.textarea-modern {
  resize: vertical;
  font-family: inherit;
}

/* Méthodes de paiement */
.payment-methods-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.method-option {
  padding: 1.25rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: var(--transition-smooth);
  background: white;
}

.method-option:hover {
  border-color: #667eea;
  transform: translateY(-2px);
}

.method-option.selected {
  border-color: #667eea;
  background: linear-gradient(135deg, #f8f9ff 0%, #f3f4ff 100%);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.method-option svg {
  color: #64748b;
}

.method-option.selected svg {
  color: #667eea;
}

.method-option span {
  font-size: 0.875rem;
  font-weight: 500;
  color: #475569;
}

.method-option.selected span {
  color: #667eea;
  font-weight: 600;
}

/* Sélecteur de mois */
.form-section-modern {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 2px solid #f1f5f9;
}

.months-selector {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.month-option {
  padding: 0.75rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  text-align: center;
  cursor: pointer;
  transition: var(--transition-smooth);
  background: white;
  font-size: 0.875rem;
  font-weight: 500;
  color: #475569;
}

.month-option:hover {
  border-color: #667eea;
  transform: translateY(-2px);
}

.month-option.selected {
  border-color: #667eea;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

/* Footer */
.modal-footer-modern {
  padding: 1.5rem 2rem;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  gap: 1rem;
}

.btn-cancel-modern,
.btn-submit-modern {
  padding: 0.875rem 1.75rem;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.938rem;
  cursor: pointer;
  transition: var(--transition-smooth);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-cancel-modern {
  background: white;
  color: #64748b;
  border: 2px solid #e2e8f0;
}

.btn-cancel-modern:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
}

.btn-submit-modern {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-submit-modern:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
}

.btn-submit-modern:disabled {
  background: #cbd5e1;
  color: #94a3b8;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

/* Responsive */
@media (max-width: 768px) {
  .modal-dialog.modal-xl {
    margin: 0.5rem;
  }

  .modal-body-modern {
    padding: 1.5rem;
  }

  .payment-types-grid,
  .students-grid-modern,
  .books-grid-modern {
    grid-template-columns: 1fr;
  }

  .payment-details-grid {
    grid-template-columns: 1fr;
  }

  .payment-methods-grid {
    grid-template-columns: 1fr;
  }

  .months-selector {
    grid-template-columns: repeat(2, 1fr);
  }

  .modal-footer-modern {
    flex-direction: column-reverse;
  }

  .btn-cancel-modern,
  .btn-submit-modern {
    width: 100%;
    justify-content: center;
  }

  .step-header-modern {
    flex-direction: row;
    align-items: flex-start;
  }
}

@media (max-width: 576px) {
  .modal-header-modern {
    padding: 1.25rem 1rem;
  }

  .modal-title {
    font-size: 1.25rem;
  }

  .modal-subtitle {
    font-size: 0.813rem;
  }

  .books-grid-modern {
    gap: 1rem;
  }

  .months-selector {
    grid-template-columns: 1fr;
  }
}

/* Utilitaires */
.d-none {
  display: none !important;
}
</style>