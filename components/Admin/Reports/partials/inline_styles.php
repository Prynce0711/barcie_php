<style>
.report-section {
  margin-bottom: 2rem;
  padding: 2rem;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  border-radius: 16px;
  border-left: 4px solid #667eea;
}

.report-section h5 {
  color: #2d3748;
  font-weight: 700;
  margin-bottom: 1.5rem;
  font-size: 1.25rem;
}

.card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border-radius: 16px;
}

.stats-card:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important;
}

.sticky-top {
  position: sticky;
  top: 0;
  z-index: 10;
  background: white;
}

.shadow-lg {
  box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
}

.bg-primary.bg-opacity-10 {
  background-color: rgba(102, 126, 234, 0.1) !important;
}

.bg-success.bg-opacity-10 {
  background-color: rgba(240, 147, 251, 0.1) !important;
}

.icon-circle {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}

.icon-box {
  opacity: 0.9;
}

.letter-spacing-1 {
  letter-spacing: 0.5px;
}

.display-6 {
  font-size: 2rem;
}

@media (max-width: 768px) {
  .display-6 {
    font-size: 1.5rem;
  }
}

canvas {
  max-height: 300px;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

#reportLoading {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
