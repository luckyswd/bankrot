declare module 'react-input-mask' {
  import { ComponentType, InputHTMLAttributes, ReactNode } from 'react';

  interface InputState {
    value: string;
    selection: {
      start: number;
      end: number;
    } | null;
  }

  interface BeforeMaskedStateChangeStates {
    previousState: InputState;
    currentState: InputState;
    nextState: InputState;
  }

  interface InputMaskProps extends Omit<InputHTMLAttributes<HTMLInputElement>, 'mask'> {
    /**
     * Маска ввода. Может быть строкой или массивом регулярных выражений.
     * Примеры: "999-999-999", "99/99/9999"
     */
    mask: string | Array<string | RegExp>;
    
    /**
     * Символ маски, который будет отображаться для незаполненных позиций.
     * По умолчанию "_". Если null, символы маски не отображаются.
     */
    maskChar?: string | null;
    
    /**
     * Формат значения после маскирования.
     * Если true, значение будет содержать только введенные символы без символов маски.
     */
    formatChars?: { [key: string]: string };
    
    /**
     * Показывать ли маску всегда, даже когда поле пустое.
     */
    alwaysShowMask?: boolean;
    
    /**
     * Функция, вызываемая перед изменением состояния маски.
     */
    beforeMaskedStateChange?: (states: BeforeMaskedStateChangeStates) => InputState;
    
    /**
     * Функция-рендер, которая получает пропсы для input элемента.
     */
    children: (inputProps: InputHTMLAttributes<HTMLInputElement>) => ReactNode;
  }

  const InputMask: ComponentType<InputMaskProps>;
  export default InputMask;
}

