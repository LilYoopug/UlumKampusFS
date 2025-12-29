// FIX: Imported React to use React.CSSProperties
import React from 'react';
import { useIntersectionObserver } from '@/hooks/useIntersectionObserver';

// FIX: Added `style` prop to allow inline styles, which is needed for the background image in the CTA section.
export const AnimatedSection: React.FC<{ children: React.ReactNode, className?: string, id?: string, style?: React.CSSProperties }> = ({ children, className = '', id, style }) => {
    const [ref, isVisible] = useIntersectionObserver({ threshold: 0.1 });
    return (
        <section ref={ref} id={id} className={`fade-in-section ${isVisible ? 'is-visible' : ''} ${className}`} style={style}>
            {children}
        </section>
    );
};