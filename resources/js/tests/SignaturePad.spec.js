import { mount } from '@vue/test-utils';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import SignaturePad from '@/components/SignaturePad.vue';

// Mock da biblioteca signature_pad
vi.mock('signature_pad', () => {
  return {
    default: vi.fn().mockImplementation(() => ({
      clear: vi.fn(),
      isEmpty: vi.fn().mockReturnValue(true),
      toDataURL: vi.fn().mockReturnValue('data:image/png;base64,test'),
      toData: vi.fn().mockReturnValue([]),
      fromData: vi.fn(),
      addEventListener: vi.fn(),
      off: vi.fn(),
      penColor: '#00d4ff',
      backgroundColor: 'rgba(0, 0, 0, 0)'
    }))
  };
});

describe('SignaturePad.vue', () => {
  let wrapper;

  beforeEach(() => {
    // Mock do canvas
    const canvasMock = {
      getContext: vi.fn().mockReturnValue({
        scale: vi.fn()
      }),
      getBoundingClientRect: vi.fn().mockReturnValue({
        width: 400,
        height: 200
      })
    };

    // Mock do window.devicePixelRatio
    Object.defineProperty(window, 'devicePixelRatio', {
      value: 1,
      writable: true
    });

    wrapper = mount(SignaturePad, {
      props: {
        width: 400,
        height: 200
      },
      global: {
        mocks: {
          nextTick: vi.fn().mockResolvedValue()
        }
      }
    });

    // Simular o canvas
    wrapper.vm.canvasRef = canvasMock;
  });

  it('renderiza corretamente', () => {
    expect(wrapper.find('.signature-pad-container').exists()).toBe(true);
    expect(wrapper.find('.signature-canvas').exists()).toBe(true);
    expect(wrapper.find('.signature-controls').exists()).toBe(true);
  });

  it('tem botões de controle', () => {
    const buttons = wrapper.findAll('button');
    const hasClearButton = buttons.some(button => button.text().includes('Limpar'));
    const hasUndoButton = buttons.some(button => button.text().includes('Desfazer'));
    
    expect(hasClearButton).toBe(true);
    expect(hasUndoButton).toBe(true);
  });

  it('emite eventos quando a assinatura muda', async () => {
    // Simular que a assinatura não está vazia
    wrapper.vm.signaturePad.isEmpty = vi.fn().mockReturnValue(false);
    
    await wrapper.vm.emitChange();
    
    expect(wrapper.emitted('update:modelValue')).toBeTruthy();
    expect(wrapper.emitted('change')).toBeTruthy();
  });

  it('limpa a assinatura corretamente', async () => {
    const clearSpy = vi.spyOn(wrapper.vm.signaturePad, 'clear');
    
    await wrapper.vm.clear();
    
    expect(clearSpy).toHaveBeenCalled();
    expect(wrapper.vm.canUndo).toBe(false);
  });

  it('desfaz a última ação quando possível', async () => {
    wrapper.vm.canUndo = true;
    wrapper.vm.signaturePad.toData = vi.fn().mockReturnValue([{ points: [] }]);
    
    await wrapper.vm.undo();
    
    expect(wrapper.vm.signaturePad.fromData).toHaveBeenCalled();
  });

  it('retorna data URL da assinatura', () => {
    wrapper.vm.signaturePad.isEmpty = vi.fn().mockReturnValue(false);
    
    const dataUrl = wrapper.vm.getSignatureDataUrl();
    
    expect(dataUrl).toBe('data:image/png;base64,test');
  });

  it('verifica se está vazio corretamente', () => {
    wrapper.vm.signaturePad.isEmpty = vi.fn().mockReturnValue(true);
    
    expect(wrapper.vm.isEmpty()).toBe(true);
  });
}); 