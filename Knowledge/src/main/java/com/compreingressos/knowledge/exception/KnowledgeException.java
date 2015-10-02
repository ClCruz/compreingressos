package com.compreingressos.knowledge.exception;

/**
 *
 * @author edicarlos.barbosa
 */
public class KnowledgeException extends Exception {

    public KnowledgeException() {
    }

    /**
     * Constructs an instance of <code>KnowledgeException</code> with the
     * specified detail message.
     *
     * @param msg the detail message.
     */
    public KnowledgeException(String msg) {
        super(msg);
    }
}
